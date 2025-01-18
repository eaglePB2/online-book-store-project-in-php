<?php
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";

// Start session
session_start();
require_once "./functions/database_functions.php";
require_once "./functions/cart_functions.php";


// Get book ISBN from POST method
$book_isbn = isset($_POST['bookisbn']) ? trim($_POST['bookisbn']) : null;

// Initialize cart session variables if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
    $_SESSION['total_items'] = 0;
    $_SESSION['total_price'] = '0.00';
}

// Add book to cart
if ($book_isbn) {
    if (!isset($_SESSION['cart'][$book_isbn])) {
        $_SESSION['cart'][$book_isbn] = 1; // Add new book with quantity 1
    } elseif (isset($_POST['cart'])) {
        $_SESSION['cart'][$book_isbn]++; // Increment quantity
    }
}

// Update cart quantities if "save changes" is clicked
if (isset($_POST['save_change'])) {
    foreach ($_SESSION['cart'] as $isbn => $qty) {
        if (isset($_POST[$isbn])) {
            $new_qty = intval($_POST[$isbn]); // Ensure the quantity is an integer
            if ($new_qty <= 0) {
                unset($_SESSION['cart'][$isbn]); // Remove item if quantity is 0
            } else {
                $_SESSION['cart'][$isbn] = $new_qty; // Update quantity
            }
        }
    }
}

// Print out header
$title = "Your shopping cart";
require "./template/header.php";

// Calculate total items and total price
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $_SESSION['total_price'] = total_price($_SESSION['cart']);
    $_SESSION['total_items'] = total_items($_SESSION['cart']);
?>
    <form action="cart.php" method="post">
        <table class="table">
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php
            foreach ($_SESSION['cart'] as $isbn => $qty) {
                $conn = db_connect();
                $book = mysqli_fetch_assoc(getBookByIsbn($conn, $isbn));
                if (!$book) {
                    echo "<tr><td colspan='4' class='text-danger'>Book not found for ISBN: $isbn</td></tr>";
                    continue;
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['book_title']) . " by " . htmlspecialchars($book['book_author']); ?></td>
                    <td><?php echo "$" . htmlspecialchars($book['book_price']); ?></td>
                    <td>
                        <input type="text" value="<?php echo htmlspecialchars($qty); ?>" size="2" name="<?php echo htmlspecialchars($isbn); ?>">
                    </td>
                    <td><?php echo "$" . htmlspecialchars($qty * $book['book_price']); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th><?php echo $_SESSION['total_items']; ?></th>
                <th><?php echo "$" . $_SESSION['total_price']; ?></th>
            </tr>
        </table>
        <input type="submit" class="btn btn-primary" name="save_change" value="Save Changes">
    </form>
    <br/><br/>
    <a href="checkout.php" class="btn btn-primary">Go To Checkout</a> 
    <a href="books.php" class="btn btn-primary">Continue Shopping</a>
<?php
} else {
    echo "<p class=\"text-warning\">Your cart is empty! Please make sure you add some books to it!</p>";
}

if (isset($conn)) {
    mysqli_close($conn);
}

require_once "./template/footer.php";
?>
