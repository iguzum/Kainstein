<?php
use App\Controllers\Order_Controller;
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// :: START STAFF ACCESS CHECK
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$staffAccount = $_SESSION['account'] ?? null;
// :: START STAFF ROLE CHECK
if (!$staffAccount || ($staffAccount['role'] ?? '') !== 'staff') {
    header('Location: ../index.php');
    exit;
}
// :: END STAFF ROLE CHECK
// :: END STAFF ACCESS CHECK

$orderController = new Order_Controller();
$queueError = '';
$completedOrder = isset($_GET['completed']) && $_GET['completed'] === '1';

// :: START COMPLETE FIRST QUEUED ORDER
if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && ($_POST['form_action'] ?? '') === 'complete_next_order'
) {
    try {
        $orderController->completeNextOrder();
        header('Location: index.php?completed=1');
        exit;
    } catch (InvalidArgumentException $error) {
        $queueError = $error->getMessage();
    } catch (Throwable $error) {
        $queueError = 'Unable to complete the next order. Check the queue database setup.';
    }
}
// :: END COMPLETE FIRST QUEUED ORDER

$pendingOrders = [];
$nextOrder = null;
try {
    $orderQueue = $orderController->getOrderQueue();
    $pendingOrders = $orderQueue->all();
    $nextOrder = $orderQueue->peek();
} catch (Throwable $error) {
    $queueError = 'Unable to load orders. Check the queue database setup.';
}
?>
<!DOCTYPE html>
<!-- :: START STAFF DASHBOARD -->
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#faf7f2">
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <title>KAINSTEIN | Staff Dashboard</title>
</head>

<body class="min-h-screen bg-background font-dm text-dark antialiased">
    <div class="min-h-screen lg:flex">
        <aside
            class="flex w-full flex-col border-b border-border bg-inner-card px-6 py-6 lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r">
            <a href="../index.php" class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-2xl bg-primary text-xl text-light">
                    <i class="bx bx-bowl-hot" aria-hidden="true"></i>
                </span>
                <span>
                    <span class="block font-playfair text-2xl font-bold tracking-wide">KAINSTEIN</span>
                    <span class="text-xs text-tertiary">Staff workspace</span>
                </span>
            </a>

            <p class="mb-3 mt-10 px-3 text-[10px] font-bold uppercase tracking-[.2em] text-tertiary">Workspace</p>
            <nav class="grid grid-cols-2 gap-2 lg:grid-cols-1" aria-label="Staff dashboard">
                <a href="#overview"
                    class="flex items-center gap-3 rounded-xl bg-outer-card px-4 py-3 text-sm font-semibold text-primary">
                    <i class="bx bx-grid-alt text-xl" aria-hidden="true"></i> Overview
                </a>
                <a href="#orders"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark">
                    <i class="bx bx-receipt text-xl" aria-hidden="true"></i> Order queue
                </a>
                <!-- :: START STAFF MENU ROOT LINK -->
                <a href="../menu.php"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark">
                    <i class="bx bx-food-menu text-xl" aria-hidden="true"></i> Menu
                </a>
                <!-- :: END STAFF MENU ROOT LINK -->
            </nav>

            <div class="mt-8 rounded-2xl bg-background p-4">
                <p class="text-xs font-bold uppercase tracking-[.16em] text-secondary">Service note</p>
                <p class="mt-2 text-sm leading-6 text-tertiary">Keep the queue moving and serve every order with care.
                </p>
            </div>

            <!-- :: START STAFF ACCOUNT LINKS -->
            <div class="mt-4 rounded-2xl bg-background p-4">
                <p class="text-xs font-bold uppercase tracking-[.16em] text-secondary">Signed in as</p>
                <!-- :: START SIGNED-IN STAFF NAME -->
                <p class="mt-2 break-all text-sm font-semibold"><?= htmlspecialchars($staffAccount['account_name'] ?? $staffAccount['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <!-- :: END SIGNED-IN STAFF NAME -->
            </div>
            <a href="../logout.php" class="mt-4 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark lg:mt-auto">
                <i class="bx bx-log-out text-xl" aria-hidden="true"></i> Log out
            </a>
            <!-- :: END STAFF ACCOUNT LINKS -->

            <!-- :: START REMOVE BACK TO ORDERING -->
            <!-- :: END REMOVE BACK TO ORDERING -->
        </aside>

        <main id="overview" class="min-w-0 flex-1 px-5 py-7 sm:px-8 lg:px-10 lg:py-10">
            <header class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.2em] text-secondary">Kainstein operations</p>
                    <h1 class="mt-2 font-playfair text-3xl font-bold sm:text-4xl">Staff dashboard</h1>
                </div>
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-border bg-inner-card px-4 py-2 text-xs font-semibold text-tertiary">
                    <span class="size-2 rounded-full bg-accent"></span> Staff view
                </span>
            </header>

            <!-- :: START ORDER QUEUE FEEDBACK -->
            <?php if ($completedOrder): ?>
                <p role="status" class="mb-5 rounded-xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm font-semibold text-primary">
                    Order completed and removed from the queue.
                </p>
            <?php endif; ?>
            <?php if ($queueError !== ''): ?>
                <p role="alert" class="mb-5 rounded-xl border border-secondary/30 bg-secondary/10 px-4 py-3 text-sm text-dark">
                    <?= htmlspecialchars($queueError, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>
            <!-- :: END ORDER QUEUE FEEDBACK -->

            <section class="mb-7 overflow-hidden rounded-3xl bg-primary px-6 py-7 text-light sm:px-9 sm:py-9">
                <p class="text-xs font-bold uppercase tracking-[.2em] text-light/70">Order service</p>
                <div class="mt-3 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                    <div>
                        <h2 class="max-w-2xl font-playfair text-3xl font-bold leading-tight sm:text-4xl">A clear queue
                            keeps service smooth.</h2>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-light/75">Your workspace for incoming orders,
                            preparation, and daily service.</p>
                    </div>
                    <a href="#orders"
                        class="inline-flex w-fit items-center gap-2 rounded-xl bg-light px-5 py-3 text-sm font-bold text-primary transition hover:bg-light-hover">
                        View order queue <i class="bx bx-right-arrow-alt text-lg" aria-hidden="true"></i>
                    </a>
                </div>
            </section>

            <!-- :: START LIVE QUEUE SUMMARY -->
            <section class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Staff overview">
                <article class="rounded-2xl border border-border bg-inner-card p-5 shadow-sm">
                    <span class="grid size-11 place-items-center rounded-xl bg-outer-card text-xl text-primary"><i
                            class="bx bx-receipt" aria-hidden="true"></i></span>
                    <p class="mt-5 text-sm font-semibold text-tertiary">Orders in queue</p>
                    <p class="mt-1 font-playfair text-3xl font-bold"><?= count($pendingOrders) ?></p>
                    <p class="mt-2 text-xs text-tertiary">Pending customer orders.</p>
                </article>
                <article class="rounded-2xl border border-border bg-inner-card p-5 shadow-sm">
                    <span class="grid size-11 place-items-center rounded-xl bg-outer-card text-xl text-secondary"><i
                            class="bx bx-loader-circle" aria-hidden="true"></i></span>
                    <p class="mt-5 text-sm font-semibold text-tertiary">Next customer</p>
                    <p class="mt-1 truncate font-playfair text-2xl font-bold">
                        <?= $nextOrder ? htmlspecialchars($nextOrder['customer_name'], ENT_QUOTES, 'UTF-8') : '—' ?>
                    </p>
                    <p class="mt-2 text-xs text-tertiary">First in the FIFO queue.</p>
                </article>
                <article class="rounded-2xl border border-border bg-inner-card p-5 shadow-sm">
                    <span class="grid size-11 place-items-center rounded-xl bg-outer-card text-xl text-tertiary"><i
                            class="bx bx-check-circle" aria-hidden="true"></i></span>
                    <p class="mt-5 text-sm font-semibold text-tertiary">Next order number</p>
                    <p class="mt-1 font-playfair text-3xl font-bold">
                        <?= $nextOrder ? '#' . (int) $nextOrder['order_id'] : '—' ?>
                    </p>
                    <p class="mt-2 text-xs text-tertiary">Oldest pending order.</p>
                </article>
            </section>
            <!-- :: END LIVE QUEUE SUMMARY -->

            <section id="orders" class="rounded-3xl border border-border bg-inner-card p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[.18em] text-secondary">Live service</p>
                        <h2 class="mt-2 font-playfair text-2xl font-bold">Order queue</h2>
                    </div>
                    <span class="rounded-full bg-outer-card px-3 py-1.5 text-xs font-semibold text-tertiary">
                        <?= count($pendingOrders) ?> pending
                    </span>
                </div>
                <!-- :: START FIFO ORDER LIST -->
                <?php if (empty($pendingOrders)): ?>
                    <div class="mt-6 rounded-2xl border border-dashed border-border bg-background px-5 py-12 text-center">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-inner-card text-2xl text-tertiary">
                            <i class="bx bx-receipt" aria-hidden="true"></i>
                        </span>
                        <h3 class="mt-4 font-semibold">The queue is empty</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-tertiary">
                            New placed orders will appear here in the order they were received.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="mt-6 space-y-4">
                        <?php foreach ($pendingOrders as $index => $order): ?>
                            <article class="rounded-2xl border <?= $index === 0 ? 'border-primary/40 bg-background' : 'border-border bg-inner-card' ?> p-5 sm:p-6">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-playfair text-xl font-bold">
                                                <?= htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </h3>
                                            <?php if ($index === 0): ?>
                                                <span class="rounded-full bg-primary px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-light">Next up</span>
                                            <?php else: ?>
                                                <span class="rounded-full bg-outer-card px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-tertiary">In line</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mt-2 text-xs font-medium text-tertiary">
                                            Order #<?= (int) $order['order_id'] ?> · <?= htmlspecialchars(ucwords(str_replace('_', ' ', $order['order_type'])), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>
                                    <div class="rounded-xl bg-outer-card px-3 py-2 text-right">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-tertiary">Order total</p>
                                        <p class="mt-1 font-bold text-primary">₱<?= number_format((float) $order['total'], 2) ?></p>
                                    </div>
                                </div>

                                <div class="mt-5 border-t border-border pt-4">
                                    <p class="mb-3 text-[10px] font-bold uppercase tracking-[.16em] text-tertiary">Order items</p>
                                    <div class="space-y-3">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <div class="flex items-center justify-between gap-4 text-sm">
                                                <div class="min-w-0">
                                                    <p class="truncate font-semibold text-dark">
                                                        <?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </p>
                                                    <p class="mt-1 text-xs text-tertiary">
                                                        <?= (int) $item['quantity'] ?> × ₱<?= number_format((float) $item['unit_price'], 2) ?>
                                                    </p>
                                                </div>
                                                <p class="shrink-0 font-semibold text-dark">₱<?= number_format((float) $item['line_total'], 2) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <?php if ($index === 0): ?>
                                    <!-- :: START COMPLETE NEXT BUTTON -->
                                    <form method="post" action="index.php" class="mt-5 border-t border-border pt-4">
                                        <input type="hidden" name="form_action" value="complete_next_order">
                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3.5 text-sm font-bold text-light transition hover:bg-primary-hover sm:w-auto">
                                            <i class="bx bx-check-circle text-lg" aria-hidden="true"></i>
                                            Mark done &amp; move to next
                                        </button>
                                    </form>
                                    <!-- :: END COMPLETE NEXT BUTTON -->
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!-- :: END FIFO ORDER LIST -->
            </section>

            <footer class="mt-8 text-center text-xs text-tertiary">© <?= date('Y') ?> KAINSTEIN · Staff workspace
            </footer>
        </main>
    </div>
</body>

</html>
<!-- :: END STAFF DASHBOARD -->
