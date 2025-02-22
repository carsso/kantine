<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\File;
use App\Http\Requests\UploadFormRequest;
use App\Jobs\ProcessFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MenuController extends Controller
{
    public function menu($dateString = null)
    {
        $dateToday = strtotime('today 10 am');
        $date = $dateToday;
        if(date('H') >= 15) {
            $date = strtotime('+1 day', $date);
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
        }
        $menu = Menu::where('date', '>=', date('Y-m-d', $date))->where('mains', '!=', '[]')->where('sides', '!=', '[]')->orderBy('date', 'asc')->first();
        if($menu) {
            $date = strtotime($menu->date.' 10 am');
        }
        $mondayTime = strtotime('monday this week 10 am', $date);
        $sundayTime = strtotime('sunday this week 10 am', $date);
        $calendarWeekFirstDay = date('Y-m-d', $mondayTime);
        $calendarWeekLastDay = date('Y-m-d', $sundayTime);
        $weekMenus = Menu::whereBetween('date', [$calendarWeekFirstDay, $calendarWeekLastDay])->get();
        $prevWeek = date('Y-m-d', strtotime('-1 week', $mondayTime));
        $nextWeek = date('Y-m-d', strtotime('+1 week', $mondayTime));
        return view('menu', ['menus' => $weekMenus, 'weekMonday' => Carbon::parse($mondayTime), 'weekSunday' => Carbon::parse($sundayTime), 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
    }

    public function dashboard(Request $request, $dateString = null)
    {
        $dateToday = strtotime('today 10 am');
        $date = $dateToday;
        if(date('H') >= 15) {
            $date = strtotime('+1 day', $date);
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
        }
        $menu = Menu::where('date', '>=', date('Y-m-d', $date))->where('mains', '!=', '[]')->where('sides', '!=', '[]')->orderBy('date', 'asc')->first();
        if($menu) {
            $date = strtotime($menu->date.' 10 am');
        }

        $day = Carbon::parse(time: $date);
        $diff = $day->diffForHumans(
            Carbon::parse($dateToday),
            [
                'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                'options' => Carbon::JUST_NOW | Carbon::ONE_DAY_WORDS | Carbon::TWO_DAY_WORDS
            ],
        );
        if($date == $dateToday)
        {
            $diff = '';
        }

        $generationDate = Carbon::now();

        $style = $menu ? $request->query('style', $menu->style): 'default';
        $particlesOptions = in_array($style, array_keys(config('tsparticles.config', []))) ? config('tsparticles.config.'.$style) : null;
        return view('dashboard', ['menu' => $menu, 'diff' => $diff, 'day' => $day, 'particlesOptions' => $particlesOptions, 'generationDate' => $generationDate]);
    }

    public function webexMenu($dateString)
    {
        $date = strtotime('today 10 am');
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString.' 10 am');
        }
        $menu = Menu::where('date', date('Y-m-d', $date))->where('mains', '!=', '[]')->where('sides', '!=', '[]')->first();
        return view('webex.menu', ['menu' => $menu, 'date' => Carbon::parse($date)]);
    }

    public function upload(UploadFormRequest $request)
    {
        $validated = $request->validated();
        if($validated['files']) {
            foreach($validated['files'] as $validatedFile)
            {
                $fileHash = sha1_file($validatedFile->path());
                $file = File::where('hash', $fileHash)->first();
                if($file) {
                    if(count($validated['files']) == 1) {
                        return $this->redirectWithError('Fichier déjà uploadé', redirect()->route('file', $file->hash));
                    } else {
                        continue;
                    }
                }
                $originalFilename = $validatedFile->getClientOriginalName();
                if(preg_match('/^[0-9a-f]{40}_(S[0-9]+-[0-9]{4}\.pdf)$/', $originalFilename, $matches)) {
                    $originalFilename = $matches[1];
                }
                if(!preg_match('/^S[0-9]+-[0-9]{4}\.pdf$/', $originalFilename, $matches)) {
                    return $this->redirectWithError('Fichier '.$originalFilename.' invalide, nom incorrect, format attendu : SXX-XXXX.pdf', redirect()->route('files'));
                }
                $fileName = $fileHash.'_'.$originalFilename;
                $filePath = $validatedFile->storeAs('uploads/menus', $fileName, 'public');
                $file = new File;
                if(auth()->user()) {
                    $file->user_id = auth()->user()->id;
                }
                $file->hash = $fileHash;
                $file->name = $originalFilename;
                $file->file_path = '/storage/' . $filePath;
                $file->save();
                ProcessFile::dispatch($file);
                if(count($validated['files']) == 1) {
                    return $this->redirectWithSuccess('Fichier uploadé', redirect()->route('file', $file->hash));
                }
            }
            return $this->redirectWithSuccess('Fichiers uploadés', redirect()->route('files'));
        }
        return $this->backWithError('Fichier invalide');
   }

   public function fileRelaunch($hash)
   {
        $file = File::where('hash', $hash)->first();
        if(!$file) {
            return $this->redirectWithError('Fichier non trouvé', redirect()->route('home'));
        }
        if($file->state != 'error' && !auth()->user()->hasRole('Super Admin')) {
            return $this->redirectWithError('Le fichier n\'est pas en erreur', redirect()->route('file', $file->hash));
        }
        $file->state = 'todo';
        $file->message = null;
        $file->save();
        ProcessFile::dispatch($file);
        return $this->redirectWithSuccess('Traitement du fichier relancé', redirect()->route('file', $file->hash));
   }

   public function fileDelete($hash)
   {
        $file = File::where('hash', $hash)->first();
        if(!$file) {
            return $this->redirectWithError('Fichier non trouvé', redirect()->route('home'));
        }
        if($file->state != 'error' && !auth()->user()->hasRole('Super Admin')) {
            return $this->redirectWithError('Fichier non en erreur', redirect()->route('file', $file->hash));
        }
        if(is_file(public_path($file->file_path))) {
            unlink(public_path($file->file_path));
        }
        if(is_file(public_path($file->csv_file_path))) {
            unlink(public_path($file->csv_file_path));
        }
        $file->delete();
        return $this->redirectWithSuccess('Fichier supprimé', redirect()->route('files'));
   }

   public function file($hash)
   {
        $file = File::where('hash', $hash)->first();
        if(!$file) {
            return $this->redirectWithError('Fichier non trouvé', redirect()->route('home'));
        }
        return view('file', ['file' => $file]);
   }

   public function files()
   {
        $files = File::all()->sortByDesc('datetime')->sortByDesc('name')->sortByDesc('filenameWeek')->sortByDesc('filenameYear');
        return view('files', ['files' => $files]);
   }

   public function notifications()
   {
        $date = strtotime('today 10 am');
        $menu = Menu::where('date', date('Y-m-d', $date))->first();
        return view('notifications', ['menu' => $menu, 'date' => Carbon::parse($date)]);
   }

   public function legal()
   {
       return view('legal');
   }
}
