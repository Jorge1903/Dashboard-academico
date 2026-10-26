<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\MetabaseEmbed;
Route::get('/dashboard/embed', function (Request $request, MetabaseEmbed $embed) {
    $input = $request->validate(['internet'=>['nullable','integer','in:0,1']]);
    try {
        return response()->json($embed->create(isset($input['internet']) ? (int) $input['internet'] : null))->header('Cache-Control','no-store, private')->header('Referrer-Policy','no-referrer');
    } catch (RuntimeException $e) {
        return response()->json(['message'=>$e->getMessage()],503)->header('Cache-Control','no-store');
    }
})->middleware('throttle:60,1');
Route::get('/summary', function () {
    $f=fopen(base_path('../data/estudiantes_limpio.csv'),'r'); $head=fgetcsv($f, escape:''); $n=0;$sum=0;$pass=0;$internet=0;$known=0;
    while (($r=fgetcsv($f, escape:''))!==false) {
        $row=array_combine($head,$r);$n++;$sum+=(float)$row['final_grade_pct'];$pass+=(float)$row['final_grade_pct']>=40?1:0;
        if ($row['has_internet_access']!=='') {$known++;$internet+=(int)$row['has_internet_access'];}
    }
    fclose($f);
    return ['students'=>$n,'average'=>round($sum/max(1,$n),1),'pass_rate'=>round(100*$pass/max(1,$n),1),'internet_rate'=>round(100*$internet/max(1,$known),1),'internet_known'=>$known];
});
Route::get('/dashboard/pdf', function () {
    $path=base_path('../dashboard-academico.pdf');abort_unless(is_file($path),404,'El PDF aún no está disponible.');
    return response()->download($path,'dashboard-academico.pdf');
});
