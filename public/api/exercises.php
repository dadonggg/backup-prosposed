<?php
header('Access-Control-Allow-Origin: *');
session_start();

$API_KEY  = '3560f98317mshc80fd375fe41dbfp19b877jsn1ae4b529e7ce';
$API_HOST = 'exercisedb.p.rapidapi.com';

$action = $_GET['action'] ?? 'all';
$query  = $_GET['q']      ?? '';
$part   = $_GET['part']   ?? '';
$equip  = $_GET['equip']  ?? '';

// ── 1. SECURE IMAGE ENDPOINT STREAMING ────────────────
if ($action === 'image') {
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Exercise ID required']);
        exit;
    }

    // Call ExerciseDB image streaming endpoint
    $url = "https://{$API_HOST}/image?exerciseId={$id}&resolution=360";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => [
            "x-rapidapi-key: {$API_KEY}",
            "x-rapidapi-host: {$API_HOST}"
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($httpCode === 200) {
        header("Content-Type: " . ($contentType ?: "image/gif"));
        header("Cache-Control: public, max-age=86400"); // Cache in browser for 24 hours
        echo $response;
        exit;
    } else {
        error_log("Failed to stream ExerciseDB image for ID {$id}: HTTP {$httpCode}");
        header("Location: https://placehold.co/200?text=No+Preview");
        exit;
    }
}

// ── 2. LOCAL CACHE LOADING AND FILTERING ─────────────
header('Content-Type: application/json');

$cacheFile = __DIR__ . '/exercisedb_cache.json';
$allExercises = [];

if (file_exists($cacheFile)) {
    $raw = file_get_contents($cacheFile);
    $allExercises = json_decode($raw, true) ?: [];
}

// If cache is present and loaded, perform super-fast local filtering
if (!empty($allExercises)) {
    switch ($action) {
        case 'search':
            $q = strtolower(trim($query));
            $filtered = array_filter($allExercises, function($ex) use ($q) {
                return strpos(strtolower($ex['name']), $q) !== false;
            });
            echo json_encode(array_values($filtered));
            exit;
            
        case 'bodypart':
            $p = strtolower(trim($part));
            $filtered = array_filter($allExercises, function($ex) use ($p) {
                return strtolower($ex['bodyPart']) === $p;
            });
            echo json_encode(array_values($filtered));
            exit;
            
        case 'equipment':
            $e = strtolower(trim($equip));
            $filtered = array_filter($allExercises, function($ex) use ($e) {
                return strtolower($ex['equipment']) === $e;
            });
            echo json_encode(array_values($filtered));
            exit;

        case 'bodyparts':
            $parts = array_unique(array_column($allExercises, 'bodyPart'));
            sort($parts);
            echo json_encode(array_values($parts));
            exit;

        case 'equipmentlist':
            $equips = array_unique(array_column($allExercises, 'equipment'));
            sort($equips);
            echo json_encode(array_values($equips));
            exit;

        case 'detail':
            $id = $_GET['id'] ?? '';
            foreach ($allExercises as $ex) {
                if ($ex['id'] === $id) {
                    echo json_encode($ex);
                    exit;
                }
            }
            http_response_code(404);
            echo json_encode(['error' => 'Exercise not found']);
            exit;

        case 'all':
        default:
            echo json_encode($allExercises);
            exit;
    }
}

// ── 3. API FALLBACK IF CACHE NOT BUILT YET ───────────
switch ($action) {
    case 'search':
        $url = "https://{$API_HOST}/exercises/name/" . urlencode(strtolower(trim($query))) . "?limit=100&offset=0";
        break;
    case 'bodypart':
        $url = "https://{$API_HOST}/exercises/bodyPart/" . urlencode(strtolower($part)) . "?limit=200&offset=0";
        break;
    case 'equipment':
        $url = "https://{$API_HOST}/exercises/equipment/" . urlencode(strtolower($equip)) . "?limit=200&offset=0";
        break;
    case 'bodyparts':
        $url = "https://{$API_HOST}/exercises/bodyPartList";
        break;
    case 'equipmentlist':
        $url = "https://{$API_HOST}/exercises/equipmentList";
        break;
    case 'detail':
        $id  = $_GET['id'] ?? '';
        $url = "https://{$API_HOST}/exercises/exercise/{$id}";
        break;
    default:
        $url = "https://{$API_HOST}/exercises?limit=10&offset=0";
        break;
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER     => [
        "x-rapidapi-key: {$API_KEY}",
        "x-rapidapi-host: {$API_HOST}",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo $response;
    exit;
}

echo $response;
?>
