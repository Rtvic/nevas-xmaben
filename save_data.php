<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Invalid request']);
  exit;
}

$file = __DIR__ . '/snowball_data.json';
if (!file_exists($file)) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Data file not found']);
  exit;
}

$data = json_decode(file_get_contents($file), true);
if (!$data) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Invalid data file']);
  exit;
}

$action = $input['action'];
$now = date('Y-m-d\TH:i:s');

switch ($action) {
  case 'toggle_task':
    $date = $input['date'] ?? '';
    $taskIdx = $input['taskIndex'] ?? -1;
    if (!isset($data['calendario'][$date]) || $taskIdx < 0) {
      http_response_code(400);
      echo json_encode(['ok'=>false,'error'=>'Invalid date or task index']);
      exit;
    }
    $task = &$data['calendario'][$date]['tasks'][$taskIdx];
    if (!$task) {
      http_response_code(400);
      echo json_encode(['ok'=>false,'error'=>'Task not found']);
      exit;
    }
    $task['done'] = !$task['done'];
    $data['ultimaActualizacion'] = $now;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true, 'done'=>$task['done'], 'action'=>'toggled']);
    exit;

  case 'set_day_state':
    $date = $input['date'] ?? '';
    $state = $input['state'] ?? '';
    if (!isset($data['calendario'][$date]) || !in_array($state, ['completado','fallido','en_curso'])) {
      http_response_code(400);
      echo json_encode(['ok'=>false,'error'=>'Invalid date or state']);
      exit;
    }
    $data['calendario'][$date]['estado'] = $state;
    $data['ultimaActualizacion'] = $now;

    $allDays = array_keys($data['calendario']);
    $completados = 0;
    $fallidos = 0;
    $enCurso = 0;
    $totalGenerado = 0;
    $totalGastado = 0;
    $racha = 0;
    $maxRacha = 0;
    $sorted = sort($allDays);

    foreach ($allDays as $d) {
      $est = $data['calendario'][$d]['estado'] ?? 'pendiente';
      if ($est === 'completado') {
        $completados++;
        $racha++;
        $totalGenerado += $data['calendario'][$d]['earned'] ?? 0;
        $totalGastado += $data['calendario'][$d]['spent'] ?? 0;
      } elseif ($est === 'fallido') {
        $fallidos++;
        $maxRacha = max($maxRacha, $racha);
        $racha = 0;
      } elseif ($est === 'en_curso') {
        $enCurso++;
      }
    }
    $maxRacha = max($maxRacha, $racha);

    $data['stats']['diasCompletados'] = $completados;
    $data['stats']['diasFallidos'] = $fallidos;
    $data['stats']['diasEnCurso'] = $enCurso;
    $data['stats']['totalGenerado'] = $totalGenerado;
    $data['stats']['totalGastado'] = $totalGastado;
    $data['stats']['rachaActual'] = $racha;
    $data['stats']['mejorRacha'] = $maxRacha;

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true, 'action'=>'state_updated']);
    exit;

  case 'log_earnings':
    $date = $input['date'] ?? '';
    $amount = intval($input['amount'] ?? 0);
    $type = $input['type'] ?? 'earned';
    if (!isset($data['calendario'][$date]) || $amount <= 0) {
      http_response_code(400);
      echo json_encode(['ok'=>false,'error'=>'Invalid date or amount']);
      exit;
    }
    if ($type === 'earned') {
      $data['calendario'][$date]['earned'] = ($data['calendario'][$date]['earned'] ?? 0) + $amount;
      $data['banco']['capital'] = ($data['banco']['capital'] ?? 0) + $amount;
    } else {
      $data['calendario'][$date]['spent'] = ($data['calendario'][$date]['spent'] ?? 0) + $amount;
      $data['banco']['capital'] = ($data['banco']['capital'] ?? 0) - $amount;
    }
    $data['ultimaActualizacion'] = $now;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true, 'capital'=>$data['banco']['capital'], 'action'=>'logged']);
    exit;

  default:
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Unknown action']);
}
