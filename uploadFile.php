<?php
// Initialize variables
$uname = "";
$uname_err = "";
$file_err = "";
$upload_err = "";
$upload_success = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $uname = trim($_POST["txt_uname"]);

    // Validate username
    if (empty($uname)) {
        $uname_err = "Please enter your username.";
    }

    // Check if a file is uploaded
    if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['uploadedFile'];

        // Check if the file is an image or PDF
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
        if (!in_array($file['type'], $allowedTypes)) {
            $file_err = "Invalid file format. Only JPEG, PNG, GIF, and PDF files are allowed.";
        }

        // Check file size (max 5MB)
        $maxFileSize = 5 * 1024 * 1024; // 5 MB in bytes
        if ($file['size'] > $maxFileSize) {
            $file_err = "File size exceeds the maximum allowed limit (5MB).";
        }

        // If there are no errors, proceed with file upload
        if (empty($file_err)) {
            $uploadDirectory = "uploads/"; // Set the path to your desired directory

            // Create the directory if it does not exist
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }
        
            $uploadedFileName = basename($file['name']);
            // generate a uniqid for file name
            $uniqueFileName = uniqid() . "_" . $uploadedFileName;
            $uploadedFile = $uploadDirectory . $uniqueFileName;
            // Get the file type from the uploaded file
            $fileType = $_FILES['uploadedFile']['type'];
            // Move the uploaded file to the destination directory
            if (move_uploaded_file($file['tmp_name'], $uploadedFile)) {
               
                // Create a database connection and insert the new upload file record into the database
                include('connect.php');
                $sql_insert_file = "INSERT INTO files (username, userfile, filetype) VALUES (?, ?, ?)";
                $stmt_insert_file = mysqli_prepare($conn, $sql_insert_file);
                mysqli_stmt_bind_param($stmt_insert_file, "sss", $uname, $uploadedFile, $fileType);
                mysqli_stmt_execute($stmt_insert_file);
                // Close the connection
                mysqli_close($conn);
                 // Set a flash message
                 session_start();
                 $_SESSION['upload_success'] = "File uploaded successfully.";
 
                 // Redirect to the same page (uploadFile.php)
                 header("Location: uploadFile.php");
                 exit; // Ensure the script stops executing after the redirect

            } else {
                // File upload failed
                $upload_err = "File upload failed. Please try again later.";
            }
        }
    } else {
        // No file uploaded
        $file_err = "Please upload your file.";
    }
}
session_start();
$upload_success = isset($_SESSION['upload_success']) ? $_SESSION['upload_success'] : "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0px;
        }

        :root {
            --main-bg: linear-gradient(90deg, #0152A1 0%, #B3B3FF 100%);
        }

        .main-bg {
            background: var(--main-bg) !important;
        }

        /* Make the input fields responsive */
        .form-control {
            width: 100%;
        }

        input:focus,
        button:focus {
            border: 1px solid lightblue !important;
            box-shadow: none !important;
        }

        .form-check-input:checked {
            background-color: var(--main-bg) !important;
            border-color: var(--main-bg) !important;
        }

        .card,
        .btn,
        input {
            border-radius: 30px !important;
        }
    </style>

</head>

<body class="main-bg d-flex justify-content-center align-items-center">
    <!-- Upload Form Form -->
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-4 col-md-6 col-sm-6 align-self-center">
                <div class="card shadow">
                    <div class="card-title text-center border-bottom">
                        <h2 class="p-3">Upload Form</h2>
                    </div>
                    <div class="card-body">
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="username" class="form-label">Username / Email</label>
                                <input type="text" class="form-control <?php echo (!empty($uname_err)) ? 'is-invalid' : ''; ?>" id="username"
                                    name="txt_uname" value="<?php echo $uname; ?>" />
                                <span class="invalid-feedback"><?php echo $uname_err; ?></span>
                            </div>
                            <div class="mb-4">
                                <label for="file" class="form-label">Upload Your File</label>
                                <input type="file" class="form-control <?php echo (!empty($file_err)) ? 'is-invalid' : ''; ?>" id="file"
                                    name="uploadedFile" />
                                <span class="invalid-feedback"><?php echo $file_err; ?></span>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn text-light main-bg" name="but_submit">Upload</button>
                            </div>
                            <?php if (!empty($upload_err)) { ?>
                            <div class="alert alert-danger mt-3" role="alert">
                                <?php echo $upload_err; ?>
                            </div>
                            <?php } ?>
                           <!-- Display the flash message if it exists -->
                           <?php if (isset($_SESSION['upload_success'])) { ?>
                            <div class="alert alert-success mt-3" role="alert">
                                <?php echo $_SESSION['upload_success']; ?>
                            </div>
                            <?php unset($_SESSION['upload_success']); }  ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
