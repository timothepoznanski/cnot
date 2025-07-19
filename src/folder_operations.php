<?php
require 'config.php';
include 'db_connect.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch($action) {
    case 'create':
        $folderName = trim($_POST['folder_name'] ?? '');
        if (empty($folderName)) {
            echo json_encode(['success' => false, 'error' => 'Folder name is required']);
            exit;
        }
        
        if ($folderName === 'Uncategorized') {
            echo json_encode(['success' => false, 'error' => 'Cannot create folder with this name']);
            exit;
        }
        
        // Check if folder already exists in entries or folders table
        $check1 = $con->query("SELECT COUNT(*) as count FROM entries WHERE folder = '" . mysqli_real_escape_string($con, $folderName) . "'");
        $check2 = $con->query("SELECT COUNT(*) as count FROM folders WHERE name = '" . mysqli_real_escape_string($con, $folderName) . "'");
        
        $result1 = $check1->fetch_assoc();
        $result2 = $check2->fetch_assoc();
        
        if ($result1['count'] > 0 || $result2['count'] > 0) {
            echo json_encode(['success' => false, 'error' => 'Folder already exists']);
        } else {
            // Create folder in folders table
            $query = "INSERT INTO folders (name) VALUES ('" . mysqli_real_escape_string($con, $folderName) . "')";
            if ($con->query($query)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database error']);
            }
        }
        break;
        
    case 'rename':
        $oldName = $_POST['old_name'] ?? '';
        $newName = trim($_POST['new_name'] ?? '');
        
        if (empty($oldName) || empty($newName)) {
            echo json_encode(['success' => false, 'error' => 'Both old and new names are required']);
            exit;
        }
        
        if ($oldName === 'Uncategorized' || $newName === 'Uncategorized') {
            echo json_encode(['success' => false, 'error' => 'Cannot rename to/from Uncategorized']);
            exit;
        }
        
        // Update entries and folders table
        $query1 = "UPDATE entries SET folder = '" . mysqli_real_escape_string($con, $newName) . "' WHERE folder = '" . mysqli_real_escape_string($con, $oldName) . "'";
        $query2 = "UPDATE folders SET name = '" . mysqli_real_escape_string($con, $newName) . "' WHERE name = '" . mysqli_real_escape_string($con, $oldName) . "'";
        
        if ($con->query($query1) && $con->query($query2)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;
        
    case 'delete':
        $folderName = $_POST['folder_name'] ?? '';
        if (empty($folderName) || $folderName === 'Uncategorized') {
            echo json_encode(['success' => false, 'error' => 'Cannot delete this folder']);
            exit;
        }
        
        // Move all notes from this folder to Uncategorized
        $query1 = "UPDATE entries SET folder = 'Uncategorized' WHERE folder = '" . mysqli_real_escape_string($con, $folderName) . "'";
        // Delete folder from folders table
        $query2 = "DELETE FROM folders WHERE name = '" . mysqli_real_escape_string($con, $folderName) . "'";
        
        if ($con->query($query1) && $con->query($query2)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;
        
    case 'move_note':
        $noteHeading = $_POST['note_heading'] ?? '';
        $targetFolder = $_POST['folder'] ?? 'Uncategorized';
        
        if (empty($noteHeading)) {
            echo json_encode(['success' => false, 'error' => 'Note heading is required']);
            exit;
        }
        
        $query = "UPDATE entries SET folder = '" . mysqli_real_escape_string($con, $targetFolder) . "' WHERE heading = '" . mysqli_real_escape_string($con, $noteHeading) . "'";
        if ($con->query($query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;
        
    case 'get_folders':
        // Get folders from both entries and folders table
        $query1 = "SELECT DISTINCT folder as name FROM entries WHERE folder IS NOT NULL AND folder != ''";
        $query2 = "SELECT name FROM folders";
        
        $result1 = $con->query($query1);
        $result2 = $con->query($query2);
        
        $folders = ['Uncategorized'];
        
        // Add folders from entries
        while($row = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
            if ($row['name'] !== 'Uncategorized' && !in_array($row['name'], $folders)) {
                $folders[] = $row['name'];
            }
        }
        
        // Add folders from folders table
        while($row = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
            if ($row['name'] !== 'Uncategorized' && !in_array($row['name'], $folders)) {
                $folders[] = $row['name'];
            }
        }
        
        // Sort folders alphabetically (Uncategorized first)
        usort($folders, function($a, $b) {
            if ($a === 'Uncategorized') return -1;
            if ($b === 'Uncategorized') return 1;
            return strcasecmp($a, $b);
        });
        
        echo json_encode(['success' => true, 'folders' => $folders]);
        break;
        
    case 'empty_folder':
        $folderName = $_POST['folder_name'] ?? '';
        
        if (empty($folderName)) {
            echo json_encode(['success' => false, 'error' => 'Folder name is required']);
            exit;
        }
        
        // Move all notes from this folder to trash
        $query = "UPDATE entries SET trash = 1 WHERE folder = '" . mysqli_real_escape_string($con, $folderName) . "' AND trash = 0";
        
        if ($con->query($query)) {
            $affected_rows = $con->affected_rows;
            echo json_encode(['success' => true, 'message' => "Moved $affected_rows notes to trash"]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error occurred']);
        }
        break;
        
    case 'count_notes_in_folder':
        $folderName = $_POST['folder_name'] ?? '';
        
        if (empty($folderName)) {
            echo json_encode(['success' => false, 'error' => 'Folder name is required']);
            exit;
        }
        
        // Count notes in this folder
        $query = "SELECT COUNT(*) as count FROM entries WHERE folder = '" . mysqli_real_escape_string($con, $folderName) . "' AND trash = 0";
        $result = $con->query($query);
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo json_encode(['success' => true, 'count' => intval($row['count'])]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error occurred']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
