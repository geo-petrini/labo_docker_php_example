<?php

require __DIR__ . '/db.php';

header('Content-Type: application/json');

$pdo = getPdo();

$action = $_GET['action'] ?? 'get_all';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'get_all':
        echo json_encode($pdo->query("SELECT * FROM items ORDER BY id")->fetchAll());
        break;

    case 'get':
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if ($item) {
            echo json_encode($item);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'non trovato']);
        }
        break;

    case 'create':
        $title = $_POST['title'] ?? '';
        $note = $_POST['note'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO items (title, note) VALUES (?, ?)");
        $stmt->execute([$title, $note]);
        echo json_encode(['id' => $pdo->lastInsertId()]);
        break;

    case 'update':
        $title = $_POST['title'] ?? '';
        $note = $_POST['note'] ?? '';
        $stmt = $pdo->prepare("UPDATE items SET title = ?, note = ? WHERE id = ?");
        $stmt->execute([$title, $note, $id]);
        echo json_encode(['ok' => $stmt->rowCount() > 0]);
        break;

    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['ok' => $stmt->rowCount() > 0]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'azione non valida']);
}