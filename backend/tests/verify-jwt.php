<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
function check($value,$message){ if(!$value) throw new RuntimeException($message); }
$secret=str_repeat('a',64);
config(['metabase.secret'=>$secret,'metabase.dashboard_id'=>39,'metabase.url'=>'http://localhost:3000']);
$service=new App\Services\MetabaseEmbed;
$data=$service->create(0);
$token=explode('#',explode('/embed/dashboard/',$data['url'])[1])[0];
[$header,$body,$sig]=explode('.',$token);
$decode=static fn($s)=>base64_decode(strtr($s,'-_','+/'));
$payload=json_decode($decode($body),true);
check(hash_equals(hash_hmac('sha256',$header.'.'.$body,$secret,true),$decode($sig)),'Invalid signature');
check($payload['resource']===['dashboard'=>39],'Wrong resource');
check($payload['exp']-time()<=300 && $payload['exp']>time(),'Invalid expiration');
check($payload['params']['internet']===0,'Filter zero was lost');
check(!str_contains(json_encode($data),$secret),'Secret exposed');
config(['metabase.secret'=>'']);
try {$service->create();throw new LogicException('Missing secret was accepted');}catch(RuntimeException $e){check(!($e instanceof LogicException),'Expected configuration error');}
echo "JWT signature, expiration, resource, filter 0 and missing-secret checks passed.\n";
