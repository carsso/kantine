<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public File $file,
    ) {
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->file->state = 'doing';
        $this->file->message = null;
        $this->file->save();

        if(!preg_match('/^S[0-9]+-([0-9]{4})\.pdf$/', $this->file->name, $matches)) {
            throw new \Exception('Fichier invalide, nom incorrect, format attendu : SXX-XXXX.pdf');
        }
        $yearFromFilename = $matches[1];

        $csvFilePath = $this->checkAndConvertPdf();

        $parser = new \Smalot\PdfParser\Parser();
        $filePath = public_path() . $this->file->file_path;
        $pdf = $parser->parseFile($filePath);

        $details = $pdf->getDetails();

        Carbon::setLocale(config('app.locale'));
        $fileDate = Carbon::parse($details['ModDate']);

        $file = fopen($csvFilePath, 'r');
        $all_data = [];
        $currentKey = null;
        $keyMappings = [
            'ENTRÉE' => 'starters',
            'PLAT' => 'mains',
            'GARNITURES' => 'sides',
            'FROMAGE / LAITAGE' => 'cheeses',
            'DESSERT' => 'desserts',
        ];
        while(($data = fgetcsv($file, 200, ",")) !== FALSE) {
            $skipLine = false;
            $str = join(',', $data);
            if(str_contains($str, 'lundi')) {
                foreach($data as $key => $value) {
                    $all_data[$key] = [
                        'date' => $value,
                        'event_name' => '',
                        'starters' => [],
                        'mains' => [],
                        'sides' => [],
                        'cheeses' => [],
                        'desserts' => [],
                    ];
                }
            }
            if(str_contains($str, 'ENTRÉE')) {
                foreach($data as $key => $value) {
                    if($value != 'ENTRÉE') {
                       $all_data[$key]['event_name'] = $value;
                    }
                }
            }
            foreach($keyMappings as $name => $keyName) {
                if(str_contains($str, $name)) {
                    $currentKey = $keyName;
                    $skipLine = true;
                }
            }
            if($currentKey && !$skipLine) {
                foreach($data as $key => $value) {
                    if($value && $value != $currentKey) {
                        array_push($all_data[$key][$currentKey], $value);
                    }
                }
            }
        }
        fclose($file);

        Carbon::setLocale(config('app.locale'));
        $message = '';
        foreach($all_data as $data) {
            # guess year from date
            $yearFound = false;
            foreach([$yearFromFilename, $yearFromFilename+1, $yearFromFilename-1] as $year) {
                Log::info('Trying to find year '.$year.' for date '.$data['date']);
                $date = Carbon::createFromLocaleFormat('l d F Y', 'fr',  $data['date'].' '.$year);
                if($date->translatedFormat('l d F') == $data['date']) {
                    $yearFound = true;
                    break;
                }
            }
            if(!$yearFound) {
                throw new \Exception('Année non trouvée');
            }
            $dateStr = $date->format('Y-m-d');
            # year found
            $menu = Menu::where('date', $dateStr)->first();
            if($menu) {
                if($menu->file->datetime_carbon && $menu->file->datetime_carbon->timestamp > $fileDate->timestamp) {
                    $message .= 'Menu plus récent déjà enregistré pour le '.$dateStr.'. ';
                    continue;
                }
            }
            if(!$menu) {
                $menu = new Menu;
            }
            $menu->date = $dateStr;
            $menu->event_name = $data['event_name'];
            $menu->starters = $data['starters'];
            $menu->mains = $data['mains'];
            $menu->sides = $data['sides'];
            $menu->cheeses = $data['cheeses'];
            $menu->desserts = $data['desserts'];
            $menu->file_id = $this->file->id;
            $menu->save();
        }

        if(!$message) {
            $message = null;
        }

        $this->file->datetime = $fileDate;
        $this->file->state = 'done';
        $this->file->message = $message;
        $this->file->save();
    }

    public function checkAndConvertPdf(): string
    {
        $parser = new \Smalot\PdfParser\Parser();
        $filePath = public_path() . $this->file->file_path;
        $pdf = $parser->parseFile($filePath);

        $text = $pdf->getText();
        $details = $pdf->getDetails();

        if(!isset($details['ModDate']) || !$details['ModDate'] || !Carbon::parse($details['ModDate']))
        {
            throw new \Exception('Fichier invalide, métadonnées incorrectes');
        }

        $wordsToCheck = ['GARNITURES', 'ENTRÉE', 'PLAT', 'FROMAGE', 'DESSERT', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        foreach ($wordsToCheck as $string) {
            if (!preg_match('/' . $string . '/', $text)) {
                throw new \Exception('Fichier '.$this->file->file_path.' invalide, ' . $string . ' non trouvé');
            }
        }

        $csvFilePath = $filePath . '.csv';
        $process = new Process(['python3', base_path('scripts/') . 'pdf2csv.py', $filePath, $csvFilePath]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $data = file_get_contents($csvFilePath);
        foreach ($wordsToCheck as $string) {
            if (!preg_match('/' . $string . '/', $data)) {
                throw new \Exception('Fichier '.$this->file->file_path.'.csv invalide, ' . $string . ' non trouvé après conversion.');
            }
        }

        return $csvFilePath;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error($exception);
        $this->file->state = 'error';
        $this->file->message = $exception->getMessage();
        $this->file->save();
    }
}
