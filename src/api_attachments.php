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
    if (!mkdir($attachments_dir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create attachments directory']);
        exit;
    }
    // Set permissions after creation
    chmod($attachments_dir, 0777);
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
    
    // Check if source file exists and is readable
    if (!is_uploaded_file($file['tmp_name'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid uploaded file']);
        return;
    }
    
    // Check if destination directory is writable
    if (!is_writable($attachments_dir)) {
        echo json_encode(['success' => false, 'message' => 'Attachments directory is not writable']);
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
        $error_msg = 'Failed to save file to: ' . $file_path;
        if (!is_dir($attachments_dir)) {
            $error_msg .= ' (directory does not exist)';
        } elseif (!is_writable($attachments_dir)) {
            $error_msg .= ' (directory not writable)';
        }
        echo json_encode(['success' => false, 'message' => $error_msg]);
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
?>
