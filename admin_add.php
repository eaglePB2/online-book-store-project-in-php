<?php
    session_start();
    require_once "./functions/admin.php";
    $title = "Add new book";
    require "./template/header.php";
    require "./functions/database_functions.php";
    $conn = db_connect();

    if (isset($_POST['add'])) {
        $isbn = trim($_POST['isbn']);
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $descr = trim($_POST['descr']);
        $price = floatval(trim($_POST['price']));
        $publisher = trim($_POST['publisher']);

        // Add image
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
            $image = $_FILES['image']['name'];
            $directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
            $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . $directory_self . "bootstrap/img/";
            $uploadDirectory .= $image;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDirectory);
        }

        // Find publisher or insert new one
        $findPub = "SELECT publisherid FROM publisher WHERE publisher_name = ?";
        $stmt = mysqli_prepare($conn, $findPub);
        if (!$stmt) {
            die("Prepare failed for SELECT publisher query: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $publisher);
        if (!mysqli_stmt_execute($stmt)) {
            die("Execution failed for SELECT publisher query: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            die("Fetching result failed for SELECT publisher query: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            // Fetch existing publisher ID
            $row = mysqli_fetch_assoc($result);
            $publisherid = $row['publisherid'];
        } else {
            // Insert new publisher and get the ID
            $insertPub = "INSERT INTO publisher (publisher_name) VALUES (?)";
            $stmt = mysqli_prepare($conn, $insertPub);
            if (!$stmt) {
                die("Prepare failed for INSERT publisher query: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "s", $publisher);
            if (!mysqli_stmt_execute($stmt)) {
                die("Execution failed for INSERT publisher query: " . mysqli_error($conn));
            }

            $publisherid = mysqli_insert_id($conn);
            if (!$publisherid) {
                die("Failed to retrieve new publisher ID.");
            }
        }

        // Insert book data
        $query = "INSERT INTO books (book_isbn, book_title, book_author, book_image, book_descr, book_price, publisherid) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            die("Prepare failed for INSERT book query: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ssssssd", $isbn, $title, $author, $image, $descr, $price, $publisherid);
        if (!mysqli_stmt_execute($stmt)) {
            die("Execution failed for INSERT book query: " . mysqli_error($conn));
        } else {
            header("Location: admin_book.php");
        }
    }
?>

	<form method="post" action="admin_add.php" enctype="multipart/form-data">
		<table class="table">
			<tr>
				<th>ISBN</th>
				<td><input type="text" name="isbn"></td>
			</tr>
			<tr>
				<th>Title</th>
				<td><input type="text" name="title" required></td>
			</tr>
			<tr>
				<th>Author</th>
				<td><input type="text" name="author" required></td>
			</tr>
			<tr>
				<th>Image</th>
				<td><input type="file" name="image"></td>
			</tr>
			<tr>
				<th>Description</th>
				<td><textarea name="descr" cols="40" rows="5"></textarea></td>
			</tr>
			<tr>
				<th>Price</th>
				<td><input type="text" name="price" required></td>
			</tr>
			<tr>
				<th>Publisher</th>
				<td><input type="text" name="publisher" required></td>
			</tr>
		</table>
		<input type="submit" name="add" value="Add new book" class="btn btn-primary">
		<input type="reset" value="cancel" class="btn btn-default">
	</form>
	<br/>
<?php
	if(isset($conn)) {mysqli_close($conn);}
	require_once "./template/footer.php";
?>