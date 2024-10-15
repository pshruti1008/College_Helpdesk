<?php
require_once "config.php";
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (!function_exists('getUserRole')) {
    function getUserRole($username, $conn) {
        $sql = "SELECT role FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    return $row['role'];
                }
            } else {
                die("Execute failed: " . mysqli_error($conn));
            }
        } else {
            die("Prepare failed: " . mysqli_error($conn));
        }
        return null; // Return null if no role is found
    }
}

function get_tickets_by_user($conn, $user_id, $role) {
    if ($role == 'department_head') {
        $sql = "SELECT t.*, u.username AS created_by_name, au.username AS assigned_to_name 
                FROM tickets t 
                LEFT JOIN users u ON t.created_by = u.id 
                LEFT JOIN users au ON t.assigned_to = au.id 
                WHERE t.assigned_to IS NULL OR t.assigned_to IN (SELECT id FROM users WHERE department = (SELECT department FROM users WHERE id = ?))";
     } else {
        $sql = "SELECT t.*, u.username AS created_by_name, au.username AS assigned_to_name 
                FROM tickets t 
                LEFT JOIN users u ON t.created_by = u.id 
                LEFT JOIN users au ON t.assigned_to = au.id 
                WHERE t.created_by = ? OR t.assigned_to = ?";
    }
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if ($role == 'department_head') {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
        }
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            die("Execute failed: " . mysqli_error($conn));
        }
    } else {
        die("Prepare failed: " . mysqli_error($conn));
    }
    return [];
}

function escalateTicket($ticket_id, $reason, $username) {
    global $conn;
    $query = "UPDATE tickets SET status = 'escalated', escalated_by = ?, escalation_reason = ? WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssi", $username, $reason, $ticket_id);
        return $stmt->execute();
    } else {
        die("Prepare failed: " . mysqli_error($conn));
    }
}

function getAssignedTickets($username) {
    global $conn;
    $sql = "SELECT t.*, u.username AS created_by_name 
            FROM tickets t 
            JOIN users u ON t.created_by = u.id 
            WHERE t.assigned_to = (SELECT id FROM users WHERE username = ?) 
            AND t.status != 'escalated'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Prepare failed: " . mysqli_error($conn));
    }
}
// function getEscalatedTickets($department) {
//     global $conn; // Assuming $conn is a mysqli object established elsewhere
  
//     $sql = "SELECT t.*, u.username AS created_by_name, e.username AS escalated_by_name 
//             FROM tickets t 
//             JOIN users u ON t.created_by = u.id 
//             JOIN users e ON t.escalated_by = e.id 
//             WHERE t.status = 'escalated' AND u.department = ?";
  
//     if ($stmt = $conn->prepare($sql)) {
//       $stmt->bind_param("s", $department);
//       $stmt->execute();
//       $result = $stmt->get_result();
//       return $result->fetch_all(MYSQLI_ASSOC);
//     } else {
//       die("Prepare failed: " . mysqli_error($conn));
//     }
//   }

function reassignTicket($ticket_id, $new_assignee) {
    global $conn;
    $sql = "UPDATE tickets SET assigned_to = ?, status = 'in_progress' WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $new_assignee, $ticket_id);
        return $stmt->execute();
    } else {
        die("Prepare failed: " . mysqli_error($conn));
    }
}

function getDepartmentStaff($hod_username) {
    global $conn;
    $department = getDepartment($hod_username, $conn);
    $sql = "SELECT username, name FROM users WHERE department = ? AND (role = 'teacher' OR role = 'staff')";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Prepare failed: " . mysqli_error($conn));
    }
}

function get_user_by_id($id, $conn) {
    $sql = "SELECT * FROM users WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            return mysqli_fetch_assoc($result);
        } else {
            die("Execute failed: " . mysqli_error($conn));
        }
    } else {
        die("Prepare failed: " . mysqli_error($conn));
    }
    return null;
}
function getUsernameById($conn, $userId) {
    if ($stmt = $conn->prepare("SELECT username FROM users WHERE id = ?")) { // Ensure $conn is a mysqli object
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['username'];
        }
    } else {
        die("Prepare failed: " . mysqli_error($conn)); // Make sure mysqli_error is used correctly
    }
    return null;
}


// function getUsernameById($conn, $userId) {
//     if ($stmt = $conn->prepare("SELECT username FROM users WHERE id = ?")) {
//         $stmt->bind_param("i", $userId);
//         $stmt->execute();
//         $result = $stmt->get_result();
//         if ($row = $result->fetch_assoc()) {
//             return $row['username'];
//         }
//     } else {
//         die("Prepare failed: " . mysqli_error($conn));
//     }
//     return null;
// }

function getDepartment($username, $conn) {
    if ($stmt = $conn->prepare("SELECT department FROM users WHERE username = ?")) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['department'];
        }
    } else {
        die("Prepare failed: " . mysqli_error($conn));
    }
    return null;
}

?>
