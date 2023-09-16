<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\File;
use App\Http\Requests\UploadFormRequest;
use App\Jobs\ProcessFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MenuController extends Controller
{
    public function index()
    {
        return $this->menu(date('Y-m-d'));
    }

    public function menu($dateString)
    {
        $date = time();
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString);
        }
        $mondayTime = strtotime('monday this week', $date);
        $sundayTime = strtotime('sunday this week', $date);
        $calendarWeekFirstDay = date('Y-m-d', $mondayTime);
        $calendarWeekLastDay = date('Y-m-d', $sundayTime);
        $weekMenus = Menu::whereBetween('date', [$calendarWeekFirstDay, $calendarWeekLastDay])->get();
        $prevWeek = date('Y-m-d', strtotime('-1 week', $mondayTime));
        $nextWeek = date('Y-m-d', strtotime('+1 week', $mondayTime));
        return view('index', ['menus' => $weekMenus, 'prevWeek' => $prevWeek, 'nextWeek' => $nextWeek]);
    }

    public function webexMenu($dateString)
    {
        $date = time();
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $date = strtotime($dateString);
        }
        $menu = Menu::where('date', date('Y-m-d', $date))->first();
         return view('webex.menu', ['menu' => $menu]);
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
                        return $this->redirectWithErrror('Fichier déjà uploadé', redirect()->route('file', $file->hash));
                    } else {
                        continue;
                    }
                }
                $fileName = $fileHash.'_'.$validatedFile->getClientOriginalName();
                $filePath = $validatedFile->storeAs('uploads/menus', $fileName, 'public');
                $file = new File;
                if(auth()->user()) {
                    $file->user_id = auth()->user()->id;
                }
                $file->hash = $fileHash;
                $file->name = $validatedFile->getClientOriginalName();
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
            return $this->redirectWithErrror('Fichier non trouvé', redirect()->route('home'));
        }
        if($file->state != 'error') {
            if(strtoupper(config('app.env')) == 'PRODUCTION') {
                return $this->redirectWithErrror('Le fichier n\'est pas en erreur', redirect()->route('file', $file->hash));
            }
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
            return $this->redirectWithErrror('Fichier non trouvé', redirect()->route('home'));
        }
        if($file->state != 'error') {
            if(strtoupper(config('app.env')) == 'PRODUCTION') {
                return $this->redirectWithErrror('Fichier non en erreur', redirect()->route('file', $file->hash));
            }
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
            return $this->redirectWithErrror('Fichier non trouvé', redirect()->route('home'));
        }
        return view('file', ['file' => $file]);
   }

   public function files()
   {
        $files = File::all()->sortByDesc('datetime')->sortByDesc('name');
        return view('files', ['files' => $files]);
   }

   public function notifications()
   {
        return view('notifications');
   }

   public function legal()
   {
       return view('legal');
   }

   public function sentry(Request $request)
   {
       if (!config('sentry.dsn')) {
           return response('Sentry is not configured', 500);
       }

       $envelope = $request->getContent();
       $pieces = explode("\n", $envelope, 2);
       $header = json_decode($pieces[0], true);

       if (empty($header['dsn']) || $header['dsn'] !== config('sentry.dsn')) {
           return response('Invalid DSN', 500);
       }

       $dsn = parse_url(config('sentry.dsn'));
       $project_id = intval(trim($dsn['path'], '/'));

       return Http::withBody($envelope, 'application/x-sentry-envelope')
           ->post('https://'.$dsn['host'].'/api/'.$project_id.'/envelope/');
   }
}
