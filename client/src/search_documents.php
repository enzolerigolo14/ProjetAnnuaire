<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$term = strtolower($_GET['q'] ?? '');
$results = [];

if (strlen($term) >= 2) {
    try {
        $stmt = $pdo->prepare("SELECT 
                                sf.file_name, 
                                sf.document_title, 
                                s.id as service_id, 
                                s.nom as service_name
                              FROM service_files sf
                              JOIN services s ON sf.service_id = s.id
                              WHERE LOWER(sf.file_name) LIKE :term 
                                 OR LOWER(sf.document_title) LIKE :term
                              LIMIT 10");
        $stmt->execute(['term' => '%'.$term.'%']);
        $files = $stmt->fetchAll();

        foreach ($files as $file) {
            $displayName = !empty($file['document_title']) 
                ? $file['document_title'] 
                : $file['file_name'];
            
            $extension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
            $isPdf = ($extension === 'pdf');
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            
            $results[] = [
                'name' => $displayName,
                'service' => $file['service_name'],
                'url' => '/projetannuaire/client/src/download.php?type=service&service_id='.$file['service_id'].'&file='.rawurlencode($file['file_name']),
                'type' => 'document',
                'file_type' => $isPdf ? 'pdf' : ($isImage ? 'image' : 'other'),
                'extension' => $extension
            ];
        }
    } catch (PDOException $e) {
        error_log("Erreur de recherche: " . $e->getMessage());
    }
}

echo json_encode(array_values($results));