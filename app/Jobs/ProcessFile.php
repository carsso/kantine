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
use Throwable;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    public $message = '';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public File $file
    ) {
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->file->state = 'doing';
        $this->file->message = '';
        $this->file->save();
        $this->message = 'Traitement du fichier '.$this->file->file_path."\n";

        if(!preg_match('/^S([0-9]+)-([0-9]{4})\.pdf$/', $this->file->name, $matches)) {
            throw new \Exception($this->message.'Fichier invalide, nom incorrect, format attendu : SXX-XXXX.pdf');
        }
        $yearFromFilename = $matches[2];
        $weekFromFilename = $matches[1];

        [$csvFilePath, $process] = $this->checkAndConvertPdf();

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
                $currentKey = 'event_name';
                $skipLine = true;
            }
            if(str_contains($str, 'ENTRÉE')) {
                foreach($data as $key => $value) {
                    if($value && $value != 'ENTRÉE') {
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
                    if($value && $value != '0.0' && $value != $currentKey) {
                        if($currentKey == 'event_name') {
                            $all_data[$key][$currentKey] = $value;
                        } else {
                            # value does not start with uppercase letter
                            if(preg_match('/^[a-z]/', $value)) {
                                if(count($all_data[$key][$currentKey]) == 0) {
                                    throw new \Exception($this->message.'Impossible de trouver début du nom du plat de type "'.$currentKey.'": "'.$value.'" pour la journée du '.$all_data[$key]['date']);
                                }
                                $value = $all_data[$key][$currentKey][count($all_data[$key][$currentKey])-1] . ' ' . $value;
                                array_pop($all_data[$key][$currentKey]);
                            }
                            array_push($all_data[$key][$currentKey], $value);
                        }
                    }
                }
            }
        }
        fclose($file);

        Carbon::setLocale(config('app.locale'));
        foreach($all_data as $data) {
            # guess year from date
            $year = $yearFromFilename;
            if($weekFromFilename > 50 && str_contains($data['date'], 'janvier')) {
                $year++;
            } else if($weekFromFilename < 3 && str_contains($data['date'], 'décembre')) {
                $year--;
            }
            $date = Carbon::createFromLocaleFormat('l d F Y', 'fr',  $data['date'].' '.$year);
            if($date->translatedFormat('l d F') != $data['date']) {
                throw new \Exception($this->message.'Impossible de trouver la journée du '.$data['date'].' en '.$year.' (semaine '.$weekFromFilename.', année '.$yearFromFilename.')');
            }
            $dateStr = $date->format('Y-m-d');
            # year found
            $menu = Menu::where('date', $dateStr)->first();
            if($menu && $menu->file) {
                if($menu->file->datetime_carbon && $menu->file->datetime_carbon->timestamp > $fileDate->timestamp) {
                    $this->message .= 'Menu plus récent déjà enregistré pour le '.$dateStr."\n";
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

        $this->file->datetime = $fileDate;
        $this->file->state = 'done';
        $this->file->message = $this->message;
        $this->file->save();
    }

    public function checkAndConvertPdf(): array
    {
        $parser = new \Smalot\PdfParser\Parser();
        $filePath = public_path() . $this->file->file_path;
        $pdf = $parser->parseFile($filePath);

        $details = $pdf->getDetails();

        if(!isset($details['ModDate']) || !$details['ModDate'] || !Carbon::parse($details['ModDate']))
        {
            throw new \Exception($this->message.'Fichier invalide, métadonnées incorrectes');
        }

        $wordsToCheck = ['GARNITURES', 'ENTRÉE', 'PLAT', 'FROMAGE', 'DESSERT', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        $csvFilePath = $filePath . '.csv';
        $process = new Process(['python3', base_path('scripts/') . 'pdf2csv.py', $filePath, $csvFilePath]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $this->message .= sprintf('Command: "%s"'."\n\nExit Code: %s(%s)\n\nWorking directory: %s\n\nOutput:\n================\n%s\n\nError Output:\n================\n%s\n\n",
            $process->getCommandLine(),
            $process->getExitCode(),
            $process->getExitCodeText(),
            $process->getWorkingDirectory(),
            $process->getOutput(),
            $process->getErrorOutput()
        );
        $data = file_get_contents($csvFilePath);
        foreach ($wordsToCheck as $string) {
            if (!preg_match('/' . $string . '/', $data)) {
                if($string == 'ENTRÉE' && $this->file->name == 'S16-2023.pdf') {
                    # exception for S16-2023.pdf
                    continue;
                }
                throw new \Exception($this->message.'Fichier '.$this->file->file_path.'.csv invalide, ' . $string . ' non trouvé après conversion.');
            }
        }

        return [$csvFilePath, $process];
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $message = $exception->getMessage() . "\n" . $exception->getTraceAsString();
        $this->file->state = 'error';
        $this->file->message = $message;
        $this->file->save();
    }
}
