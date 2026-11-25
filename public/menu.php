<?php
use App\Controllers\Menu_Controller;
use App\Controllers\Cart_Controller;
use App\Controllers\Order_Controller;
use App\Structures\Bubble_Sort;

// :: START MENU ROOT AUTOLOAD PATH
require_once dirname(__DIR__) . '/vendor/autoload.php';
// :: END MENU ROOT AUTOLOAD PATH

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// :: START MENU PAGE DATA
$controller = new Menu_Controller();
$cartController = new Cart_Controller();
$orderController = new Order_Controller();
$cartId = (int) ($_SESSION['cart_id'] ?? 0);
$selectedCategory = isset($_GET['category']) && is_string($_GET['category'])
    ? strtolower(trim($_GET['category']))
    : 'all';
// :: START DATABASE CATEGORY TITLES
$categoryTitles = [
    'all' => 'Our menu',
    'main_dishes' => 'Main Dishes',
    'desserts' => 'Desserts',
    'starter' => 'Starter',
    'sides' => 'Sides',
    'drinks' => 'Drinks',
];
// :: END DATABASE CATEGORY TITLES

if (!array_key_exists($selectedCategory, $categoryTitles)) {
    $selectedCategory = 'all';
}

$sortDirection = isset($_GET['sort']) && is_string($_GET['sort']) && in_array($_GET['sort'], ['asc', 'desc'], true)
    ? $_GET['sort']
    : '';
$menuUrl = '?category=' . urlencode($selectedCategory);
if ($sortDirection !== '') {
    $menuUrl .= '&sort=' . urlencode($sortDirection);
}
$nextSortDirection = $sortDirection === 'asc' ? 'desc' : 'asc';
$sortButtonLabel = $sortDirection === 'asc'
    ? 'Price: Low to high'
    : ($sortDirection === 'desc' ? 'Price: High to low' : 'Sort by price');
$sortButtonUrl = '?category=' . urlencode($selectedCategory) . '&sort=' . $nextSortDirection;

$sectionTitle = $categoryTitles[$selectedCategory];
$loadError = false;
$cartError = '';
$orderError = '';
$showOrderModal = false;

// :: START CART ADD SUBMISSION TOKEN
if (empty($_SESSION['cart_add_token'])) {
    $_SESSION['cart_add_token'] = bin2hex(random_bytes(32));
}
$cartAddToken = $_SESSION['cart_add_token'];
// :: END CART ADD SUBMISSION TOKEN

// :: START ADD MENU ITEM TO CART
if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && ($_POST['form_action'] ?? '') === 'add_to_cart'
) {
    $menuId = filter_var($_POST['menu_id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);

    try {
        // :: START ACCEPT CART ADD SUBMISSION ONCE
        $submittedToken = (string) ($_POST['cart_add_token'] ?? '');
        if (!hash_equals($cartAddToken, $submittedToken)) {
            header('Location: menu.php' . $menuUrl . '&added=1');
            exit;
        }
        unset($_SESSION['cart_add_token']);
        // :: END ACCEPT CART ADD SUBMISSION ONCE

        if ($cartId < 1 || $menuId === false || $quantity === false || $quantity < 1 || $quantity > 99) {
            throw new InvalidArgumentException('Please choose a valid item quantity.');
        }

        $cartController->addToCart($cartId, $menuId, $quantity);
        header('Location: menu.php' . $menuUrl . '&added=1');
        exit;
    } catch (Throwable $error) {
        $cartError = 'Unable to add this item. Please try again.';
    }
}
// :: END ADD MENU ITEM TO CART

// :: START REMOVE CART ITEM REQUEST
if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && ($_POST['form_action'] ?? '') === 'remove_cart_item'
) {
    $menuId = filter_var($_POST['menu_id'] ?? null, FILTER_VALIDATE_INT);

    try {
        if ($cartId < 1 || $menuId === false) {
            throw new InvalidArgumentException('Invalid cart item.');
        }

        $cartController->removeFromCart($cartId, $menuId);
        header('Location: menu.php' . $menuUrl . '&cart=updated');
        exit;
    } catch (Throwable $error) {
        $cartError = 'Unable to remove this item. Please try again.';
    }
}
// :: END REMOVE CART ITEM REQUEST

// :: START PLACE ORDER REQUEST
if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && ($_POST['form_action'] ?? '') === 'place_order'
) {
    $customerName = isset($_POST['customer_name']) && is_string($_POST['customer_name'])
        ? $_POST['customer_name']
        : '';

    try {
        $orderController->placeOrder(
            (int) ($_SESSION['order_id'] ?? 0),
            (int) ($_SESSION['customer_id'] ?? 0),
            $cartId,
            $customerName
        );

        unset($_SESSION['order_id'], $_SESSION['customer_id'], $_SESSION['cart_id'], $_SESSION['order_type']);
        // :: START MENU ORDER SUCCESS REDIRECT
        header('Location: index.php?order=placed');
        // :: END MENU ORDER SUCCESS REDIRECT
        exit;
    } catch (InvalidArgumentException $error) {
        $orderError = $error->getMessage();
        $showOrderModal = true;
    } catch (Throwable $error) {
        $orderError = 'Unable to place your order. Please try again.';
        $showOrderModal = true;
    }
}
// :: END PLACE ORDER REQUEST

// :: START REFRESH CART ADD TOKEN FOR PAGE
if (empty($_SESSION['cart_add_token'])) {
    $_SESSION['cart_add_token'] = bin2hex(random_bytes(32));
}
$cartAddToken = $_SESSION['cart_add_token'];
// :: END REFRESH CART ADD TOKEN FOR PAGE

try {
    $filteredProducts = $controller->getMenuByCategory($selectedCategory);
    // :: START SORT MENU PRICES WITH BUBBLE SORT
    if ($sortDirection !== '') {
        $filteredProducts = (new Bubble_Sort())->sortByPrice($filteredProducts, $sortDirection);
    }
    // :: END SORT MENU PRICES WITH BUBBLE SORT
} catch (Throwable $error) {
    $filteredProducts = [];
    $loadError = true;
}

$cartItems = [];
$cartTotal = 0;
try {
    $cartItems = $cartController->getCartItems($cartId);
    foreach ($cartItems as $cartItem) {
        $cartTotal += (float) $cartItem['line_total'];
    }
} catch (Throwable $error) {
    $cartError = 'Unable to load your cart right now.';
}
// :: END MENU PAGE DATA
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#faf7f2">
    <title>KAINSTEIN | Select Your Order</title>
    <!-- :: START MENU ROOT ASSET PATH -->
    <link rel="stylesheet" href="./src/output.css">
    <!-- :: END MENU ROOT ASSET PATH -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-background font-dm text-dark antialiased">
    <div id="mobileOverlay" class="fixed inset-0 z-40 hidden bg-dark/40 backdrop-blur-[2px] lg:hidden"></div>

    <div class="min-h-screen lg:flex">
        <!-- :: START SIDEBAR -->
        <!-- :: START MENU ROOT SIDEBAR INCLUDE -->
        <?php include __DIR__ . "/includes/sidebar.php"; ?>
        <!-- :: END MENU ROOT SIDEBAR INCLUDE -->
        <!-- :: END SIDEBAR -->

        <main class="min-w-0 flex-1 px-4 pb-12 pt-5 sm:px-7 lg:px-10 lg:pt-8 xl:px-14">
            <!-- HEADER -->
            <header class="mb-9 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button id="menuToggle"
                        class="grid size-11 place-items-center rounded-xl border border-border bg-inner-card text-2xl lg:hidden"
                        aria-label="Open navigation">
                        <i class="bx bx-menu"></i>
                    </button>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-secondary">KAINSTEIN kitchen</p>
                        <h1 class="mt-1 font-playfair text-3xl font-bold sm:text-4xl">
                            A menu worth slowing down for.
                        </h1>
                    </div>
                </div>

                <button id="cartToggle"
                    class="relative grid size-12 shrink-0 place-items-center rounded-2xl bg-primary text-2xl text-light shadow-lg shadow-primary/15 transition hover:bg-primary-hover"
                    aria-label="Open cart">
                    <i class="bx bx-shopping-bag"></i>
                    <!-- :: START CART ITEM COUNT -->
                    <?php if (!empty($cartItems)): ?>
                        <span
                            class="absolute -right-1 -top-1 grid size-5 place-items-center rounded-full bg-secondary text-xs font-bold text-light">
                            <?= array_sum(array_column($cartItems, 'quantity')) ?>
                        </span>
                    <?php endif; ?>
                    <!-- :: END CART ITEM COUNT -->
                </button>
            </header>

            <!-- :: START CART ERROR MESSAGE -->
            <?php if ($cartError !== ''): ?>
                <p role="alert"
                    class="mb-5 rounded-xl border border-secondary/30 bg-secondary/10 px-4 py-3 text-sm text-dark">
                    <?= htmlspecialchars($cartError, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>
            <!-- :: END CART ERROR MESSAGE -->

            <!-- HERO SECTION -->
            <section
                class="mb-8 flex flex-col justify-between gap-4 rounded-3xl bg-primary px-6 py-7 text-light sm:flex-row sm:items-center sm:px-9">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-light/65">
                        The good stuff starts here
                    </p>
                    <h2 class="mt-2 max-w-lg font-playfair text-3xl leading-tight sm:text-4xl">
                        Something delicious<br class="hidden sm:block"> is waiting for you.
                    </h2>
                    <p class="mt-3 max-w-md text-sm leading-6 text-light/75">
                        Explore our kitchen favorites, made fresh and served with love.
                    </p>
                </div>
                <span
                    class="hidden size-28 shrink-0 items-center justify-center rounded-full border border-light/20 bg-light/10 text-6xl text-accent sm:flex">
                    <i class="bx bx-bowl-rice"></i>
                </span>
            </section>

            <!-- :: START database-menu-heading -->
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.18em] text-tertiary">
                        Fresh from our kitchen
                    </p>
                    <h2 class="mt-1 font-playfair text-2xl font-bold">
                        <?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                </div>
                <!-- :: START MENU PRICE SORT CONTROL -->
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-xs text-tertiary"><?= count($filteredProducts) ?> delicious choices</p>
                    <a href="<?= htmlspecialchars($sortButtonUrl, ENT_QUOTES, 'UTF-8') ?>"
                        class="inline-flex items-center gap-2 rounded-xl border border-border bg-inner-card px-4 py-2.5 text-sm font-semibold text-dark transition hover:border-primary hover:text-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                        <i class="bx bx-sort" aria-hidden="true"></i>
                        <?= htmlspecialchars($sortButtonLabel, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>
                <!-- :: END MENU PRICE SORT CONTROL -->
            </div>
            <!-- :: END database-menu-heading -->

            <!-- :: START database-menu-cards -->
            <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4" aria-live="polite">
                <?php if ($loadError): ?>
                    <p
                        class="col-span-full rounded-2xl border border-dashed border-border p-12 text-center text-sm text-tertiary">
                        Unable to load menu from database.
                    </p>
                <?php elseif (empty($filteredProducts)): ?>

                    <p
                        class="col-span-full rounded-2xl border border-dashed border-border p-12 text-center text-sm text-tertiary">
                        No menu items available in this category.
                    </p>

                <?php else: ?>
                    <?php foreach ($filteredProducts as $product): ?>
                        <!-- :: START CHECK MENU AVAILABILITY -->
                        <?php $isAvailable = strtoupper((string) ($product['is_available'] ?? '')) === 'Y'; ?>
                        <!-- :: END CHECK MENU AVAILABILITY -->
                        <!-- :: START card-as-form -->
                        <form method="post" action="<?= htmlspecialchars($menuUrl, ENT_QUOTES, 'UTF-8') ?>"
                            class="group overflow-hidden rounded-2xl border border-border bg-inner-card transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-dark/5">
                            <!-- :: START DATABASE MENU FIELD NAMES -->
                            <input type="hidden" name="form_action" value="add_to_cart">
                            <input type="hidden" name="menu_id" value="<?= (int) $product['menu_id'] ?>">
                            <!-- :: START CART ADD TOKEN FIELD -->
                            <input type="hidden" name="cart_add_token"
                                value="<?= htmlspecialchars($cartAddToken, ENT_QUOTES, 'UTF-8') ?>">
                            <!-- :: END CART ADD TOKEN FIELD -->

                            <div class="relative h-75 overflow-hidden bg-outer-card">
                                <!-- :: START UNAVAILABLE FOOD INDICATOR -->
                                <?php if (!$isAvailable): ?>
                                    <span class="absolute left-4 top-4 z-10 inline-flex items-center gap-2 rounded-full border border-light/20 bg-dark/80 px-3 py-2 text-xs font-bold text-light shadow-lg">
                                        <i class="bx bx-x-circle text-base" aria-hidden="true"></i>
                                        Unavailable
                                    </span>
                                <?php endif; ?>
                                <!-- :: END UNAVAILABLE FOOD INDICATOR -->
                                <?php if (!empty($product['image_url'])): ?>
                                    <img class="select-none pointer-events-none"
                                        src="<?= htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($product['item_name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <?php else: ?>
                                    <div class="flex h-full items-center justify-center text-4xl text-tertiary">
                                        <i class="bx bx-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-playfair text-xl font-bold leading-snug">
                                        <?= htmlspecialchars($product['item_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </h3>
                                    <span class="shrink-0 text-lg font-bold text-secondary">
                                        ₱<?= number_format($product['price'], 2) ?>
                                    </span>
                                </div>

                                <p class="mt-2 min-h-10 text-sm leading-5 text-tertiary">
                                    <?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <!-- :: START buy-button-and-qty -->
                                <div class="mt-4 flex items-center justify-between gap-2">
                                    <label for="qty-<?= (int) $product['menu_id'] ?>" class="sr-only">Quantity</label>
                                    <input id="qty-<?= (int) $product['menu_id'] ?>" type="number" name="quantity" value="1"
                                        min="1" max="99" required <?= $isAvailable ? '' : 'disabled' ?>
                                        class="w-20 appearance-auto rounded-lg border border-border bg-background px-3 py-2 text-center text-sm font-semibold">
                                    <!-- :: START AVAILABILITY AWARE ADD BUTTON -->
                                    <button type="submit" <?= $isAvailable ? '' : 'disabled' ?>
                                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-xs font-bold text-light transition hover:bg-primary-hover disabled:cursor-not-allowed disabled:bg-tertiary/50">
                                        <i class="<?= $isAvailable ? 'bx bx-cart-add' : 'bx bx-x-circle' ?> text-base" aria-hidden="true"></i>
                                        <?= $isAvailable ? 'Add to cart' : 'Unavailable' ?>
                                    </button>
                                    <!-- :: END AVAILABILITY AWARE ADD BUTTON -->
                                </div>
                                <!-- :: END buy-button-and-qty -->
                            </div>
                            <!-- :: END DATABASE MENU FIELD NAMES -->
                        </form>
                        <!-- :: END card-as-form -->
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <!-- :: END database-menu-cards -->
        </main>
    </div>

    <!-- :: START cart-drawer -->
    <div id="cartBackdrop" class="fixed inset-0 z-60 hidden bg-dark/40 backdrop-blur-[2px]"></div>

    <aside id="cartDrawer"
        class="fixed inset-y-0 right-0 z-70 flex w-full max-w-110 translate-x-full flex-col bg-inner-card shadow-2xl transition-transform duration-300 ease-out"
        aria-label="Shopping cart" aria-hidden="true">
        <div class="flex items-center justify-between border-b border-border px-6 py-5">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[.2em] text-secondary">Your selection</p>
                <h2 class="mt-1 font-playfair text-2xl font-bold">Your cart</h2>
            </div>
            <button id="closeCart"
                class="grid size-10 place-items-center rounded-full bg-outer-card text-xl transition hover:bg-background-hover"
                aria-label="Close cart">
                <i class="bx bx-x"></i>
            </button>
        </div>

        <!-- :: START CART ITEM DISPLAY -->
        <div class="flex flex-1 flex-col overflow-y-auto px-6 py-5">
            <?php if (empty($cartItems)): ?>
                <div class="flex flex-1 flex-col items-center justify-center text-center">
                    <span class="grid size-20 place-items-center rounded-full bg-outer-card text-4xl text-tertiary">
                        <i class="bx bx-shopping-bag"></i>
                    </span>
                    <p class="mt-5 font-playfair text-xl font-bold">Your cart is waiting</p>
                    <p class="mt-2 max-w-55 text-xs leading-5 text-tertiary">
                        Add something delicious and it will show up here.
                    </p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($cartItems as $cartItem): ?>
                        <div class="flex items-start justify-between gap-4 border-b border-border pb-4">
                            <div class="min-w-0">
                                <p class="font-semibold text-dark">
                                    <?= htmlspecialchars($cartItem['item_name'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <p class="mt-1 text-xs text-tertiary">
                                    <?= (int) $cartItem['quantity'] ?> × ₱<?= number_format((float) $cartItem['price'], 2) ?>
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-dark">
                                ₱<?= number_format((float) $cartItem['line_total'], 2) ?>
                            </p>
                        </div>
                        <!-- :: START REMOVE ITEM BUTTON -->
                        <form method="post" action="<?= htmlspecialchars($menuUrl, ENT_QUOTES, 'UTF-8') ?>"
                            class="-mt-3 flex justify-end">
                            <input type="hidden" name="form_action" value="remove_cart_item">
                            <input type="hidden" name="menu_id" value="<?= (int) $cartItem['menu_id'] ?>">
                            <button type="submit"
                                class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-tertiary transition hover:bg-background-hover hover:text-secondary">
                                <i class="bx bx-trash" aria-hidden="true"></i> Remove
                            </button>
                        </form>
                        <!-- :: END REMOVE ITEM BUTTON -->
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($cartItems)): ?>
            <div class="border-t border-border px-6 py-5">
                <div class="flex items-center justify-between text-sm font-bold text-dark">
                    <span>Total</span>
                    <span>₱<?= number_format($cartTotal, 2) ?></span>
                </div>
                <!-- :: START PLACE ORDER BUTTON -->
                <button id="placeOrderOpen" type="button"
                    class="mt-5 w-full rounded-xl bg-primary px-5 py-3.5 text-sm font-bold text-light transition hover:bg-primary-hover">
                    Place order <span class="ml-2" aria-hidden="true">→</span>
                </button>
                <!-- :: END PLACE ORDER BUTTON -->
            </div>
        <?php endif; ?>
        <!-- :: END CART ITEM DISPLAY -->
    </aside>
    <!-- :: END cart-drawer -->

    <!-- :: START PLACE ORDER NAME MODAL -->
    <div id="placeOrderModal"
        class="fixed inset-0 z-80 hidden items-center justify-center overflow-y-auto bg-dark/50 p-4 backdrop-blur-sm"
        aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="placeOrderTitle">
        <div class="my-auto w-full max-w-md rounded-2xl border border-border bg-inner-card p-6 shadow-2xl sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-secondary">Almost there</p>
                    <h2 id="placeOrderTitle" class="mt-2 font-playfair text-2xl font-bold text-dark">Place your order
                    </h2>
                    <p class="mt-2 text-sm text-tertiary">Enter your name so we can identify your order.</p>
                </div>
                <button id="placeOrderClose" type="button" aria-label="Close name dialog"
                    class="grid size-10 shrink-0 place-items-center rounded-full bg-outer-card text-xl text-dark hover:bg-background-hover">
                    <i class="bx bx-x" aria-hidden="true"></i>
                </button>
            </div>

            <?php if ($orderError !== ''): ?>
                <p role="alert"
                    class="mt-5 rounded-xl border border-secondary/30 bg-secondary/10 px-4 py-3 text-sm text-dark">
                    <?= htmlspecialchars($orderError, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($menuUrl, ENT_QUOTES, 'UTF-8') ?>" class="mt-6">
                <input type="hidden" name="form_action" value="place_order">
                <label for="customerName" class="mb-2 block text-sm font-semibold text-dark">Your name</label>
                <input id="customerName" name="customer_name" type="text" maxlength="50" required autocomplete="name"
                    value="<?= htmlspecialchars(is_string($customerName ?? null) ? ($customerName ?? '') : '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter your name"
                    class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm text-dark outline-none transition placeholder:text-tertiary/70 focus:border-primary focus:ring-4 focus:ring-primary/10">
                <button type="submit"
                    class="mt-5 w-full rounded-xl bg-primary px-5 py-3.5 text-sm font-bold text-light transition hover:bg-primary-hover">
                    Confirm order
                </button>
            </form>
        </div>
    </div>
    <!-- :: END PLACE ORDER NAME MODAL -->

    <?php if ($showOrderModal): ?>
        openPlaceOrderModal();
    <?php endif; ?>
    <!-- :: START open-close-toggle-only -->
    <script src="./src/main.js" defer></script>
    <!-- :: END open-close-toggle-only -->
</body>

</html>
