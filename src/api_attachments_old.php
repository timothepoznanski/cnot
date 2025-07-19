<?php
// Prevent any output before JSON response
ob_start();

// Set JSON content type header
header('Content-Type: application/json');

// Disable error display (errors should be logged, not displayed)
ini_set('display_errors', 0);
error_reporting(0);

require 'config.php';
include 'db_connect.php';

// Create attachments directory if it doesn't exist
$attachments_dir = 'attachments';
if (!file_exists($attachments_dir)) {
    mkdir($attachments_dir, 0755, true);
}

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Clean any output buffer before responding
ob_clean();

switch ($action) {
    case 'upload':
        handleUpload();
        break;
    case 'list':
        handleList();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'download':
        handleDownload();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// End output buffering
ob_end_flush();

function handleUpload() {
    global $con, $attachments_dir;
    
    $note_id = $_POST['note_id'] ?? '';
    
    if (empty($note_id)) {
        echo json_encode(['success' => false, 'message' => 'Note ID is required']);
        return;
    }
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
        return;
    }
    
    $file = $_FILES['file'];
    $original_name = $file['name'];
    $file_size = $file['size'];
    $file_type = $file['type'];
    
    // Generate unique filename
    $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $attachments_dir . '/' . $unique_filename;
    
    // Validate file type (basic security)
    $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed']);
        return;
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Get current attachments
        $query = "SELECT attachments FROM entries WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_attachments = $row['attachments'] ? json_decode($row['attachments'], true) : [];
            
            // Add new attachment
            $new_attachment = [
                'id' => uniqid(),
                'filename' => $unique_filename,
                'original_filename' => $original_name,
                'file_size' => $file_size,
                'file_type' => $file_type,
                'uploaded_at' => date('Y-m-d H:i:s')
            ];
            
            $current_attachments[] = $new_attachment;
            
            // Update database
            $update_query = "UPDATE entries SET attachments = ? WHERE id = ?";
            $update_stmt = $con->prepare($update_query);
            $attachments_json = json_encode($current_attachments);
            $update_stmt->bind_param("si", $attachments_json, $note_id);
            
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
            } else {
                unlink($file_path); // Clean up file if database update fails
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
        } else {
            unlink($file_path);
            echo json_encode(['success' => false, 'message' => 'Note not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    }
}

function handleList() {
    global $con;
    
    $note_id = $_GET['note_id'] ?? '';
    
    if (empty($note_id)) {
        echo json_encode(['success' => false, 'message' => 'Note ID is required']);
        return;
    }
    
    $query = "SELECT attachments FROM entries WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $attachments = $row['attachments'] ? json_decode($row['attachments'], true) : [];
        echo json_encode(['success' => true, 'attachments' => $attachments]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Note not found']);
    }
}

function handleDelete() {
    global $con, $attachments_dir;
    
    $note_id = $_POST['note_id'] ?? '';
    $attachment_id = $_POST['attachment_id'] ?? '';
    
    if (empty($note_id) || empty($attachment_id)) {
        echo json_encode(['success' => false, 'message' => 'Note ID and Attachment ID are required']);
        return;
    }
    
    // Get current attachments
    $query = "SELECT attachments FROM entries WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $attachments = $row['attachments'] ? json_decode($row['attachments'], true) : [];
        
        // Find and remove attachment
        $file_to_delete = null;
        $updated_attachments = [];
        
        foreach ($attachments as $attachment) {
            if ($attachment['id'] === $attachment_id) {
                $file_to_delete = $attachments_dir . '/' . $attachment['filename'];
            } else {
                $updated_attachments[] = $attachment;
            }
        }
        
        if ($file_to_delete) {
            // Delete physical file
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
            
            // Update database
            $update_query = "UPDATE entries SET attachments = ? WHERE id = ?";
            $update_stmt = $con->prepare($update_query);
            $attachments_json = json_encode($updated_attachments);
            $update_stmt->bind_param("si", $attachments_json, $note_id);
            
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Attachment deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Attachment not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Note not found']);
    }
}

function handleDownload() {
    global $con, $attachments_dir;
    
    $note_id = $_GET['note_id'] ?? '';
    $attachment_id = $_GET['attachment_id'] ?? '';
    
    if (empty($note_id) || empty($attachment_id)) {
        http_response_code(400);
        exit('Note ID and Attachment ID are required');
    }
    
    // Get attachment info
    $query = "SELECT attachments FROM entries WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $attachments = $row['attachments'] ? json_decode($row['attachments'], true) : [];
        
        foreach ($attachments as $attachment) {
            if ($attachment['id'] === $attachment_id) {
                $file_path = $attachments_dir . '/' . $attachment['filename'];
                
                if (file_exists($file_path)) {
                    // Clear any previous output
                    ob_end_clean();
                    
                    // Set headers for file download
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $attachment['original_filename'] . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file_path));
                    
                    // Output file
                    readfile($file_path);
                    exit;
                } else {
                    http_response_code(404);
                    exit('File not found');
                }
            }
        }
        
        http_response_code(404);
        exit('Attachment not found');
    } else {
        http_response_code(404);
        exit('Note not found');
    }
}
                    return;
                }
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Attachment not found']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Note not found']);
    }
}
?>
    $tmp_name = $file['tmp_name'];
    
    // Validate file size (max 10MB)
    if ($file_size > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
        return;
    }
    
    // Validate file extension
    $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
    $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed']);
        return;
    }
    
    // Generate unique filename
    $unique_name = uniqid() . '_' . $original_name;
    $file_path = $attachments_dir . '/' . $unique_name;
    
    // Move uploaded file
    if (!move_uploaded_file($tmp_name, $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        return;
    }
    
    // Save to database
    $stmt = $con->prepare("INSERT INTO attachments (note_id, original_name, file_name, file_size, file_type, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $note_id, $original_name, $unique_name, $file_size, $file_type);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } else {
        // Remove file if database insert failed
        unlink($file_path);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function handleList() {
    global $con;
    
    $note_id = $_GET['note_id'] ?? '';
    
    if (empty($note_id)) {
        echo json_encode(['success' => false, 'message' => 'Note ID is required']);
        return;
    }
    
    $stmt = $con->prepare("SELECT id, original_name, file_size, file_type, uploaded_at FROM attachments WHERE note_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $attachments = [];
    while ($row = $result->fetch_assoc()) {
        $attachments[] = $row;
    }
    
    echo json_encode(['success' => true, 'attachments' => $attachments]);
}

function handleDelete() {
    global $con, $attachments_dir;
    
    $attachment_id = $_POST['attachment_id'] ?? '';
    
    if (empty($attachment_id)) {
        echo json_encode(['success' => false, 'message' => 'Attachment ID is required']);
        return;
    }
    
    // Get file info
    $stmt = $con->prepare("SELECT file_name FROM attachments WHERE id = ?");
    $stmt->bind_param("i", $attachment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attachment = $result->fetch_assoc();
    
    if (!$attachment) {
        echo json_encode(['success' => false, 'message' => 'Attachment not found']);
        return;
    }
    
    // Delete from database
    $stmt = $con->prepare("DELETE FROM attachments WHERE id = ?");
    $stmt->bind_param("i", $attachment_id);
    
    if ($stmt->execute()) {
        // Delete file
        $file_path = $attachments_dir . '/' . $attachment['file_name'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        echo json_encode(['success' => true, 'message' => 'Attachment deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
?>
