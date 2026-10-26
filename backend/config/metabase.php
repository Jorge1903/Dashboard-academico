<?php
return ['url' => env('METABASE_SITE_URL', 'http://localhost:3000'), 'secret' => env('METABASE_EMBEDDING_SECRET_KEY'), 'dashboard_id' => (int) env('METABASE_DASHBOARD_ID', 0)];
