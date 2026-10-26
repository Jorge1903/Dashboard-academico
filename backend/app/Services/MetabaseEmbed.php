<?php
namespace App\Services;
use RuntimeException;
final class MetabaseEmbed
{
    public function create(?int $internet = null): array
    {
        $secret = (string) config('metabase.secret');
        $id = (int) config('metabase.dashboard_id');
        $base = rtrim((string) config('metabase.url'), '/');
        if (!preg_match('/^[a-f0-9]{64}$/i', $secret) || $id < 1 || !filter_var($base, FILTER_VALIDATE_URL) || !in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new RuntimeException('Configura el ID del dashboard y la clave de incrustación en backend/.env.');
        }
        $expires = time() + 300;
        $encode = static fn ($s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
        $head = $encode(json_encode(['alg'=>'HS256','typ'=>'JWT'], JSON_THROW_ON_ERROR));
        $body = $encode(json_encode(['resource'=>['dashboard'=>$id],'params'=>['internet'=>$internet===null?[]:$internet],'iat'=>time(),'exp'=>$expires], JSON_THROW_ON_ERROR));
        $token = $head.'.'.$body.'.'.$encode(hash_hmac('sha256', $head.'.'.$body, $secret, true));
        return ['url'=>$base.'/embed/dashboard/'.$token.'#bordered=false&titled=false','expires_at'=>$expires];
    }
}
