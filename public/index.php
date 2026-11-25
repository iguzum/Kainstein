<?php
require '../vendor/autoload.php';
use App\Controllers\Create_Customer_Controller;

// :: START ORDER TYPE SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// :: END ORDER TYPE SESSION

$error = null;
// :: START LOGIN FEEDBACK STATE
$loginFailed = isset($_GET['login']);
// :: END LOGIN FEEDBACK STATE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new Create_Customer_Controller();
    $error = $controller->create();
}
?>

<!DOCTYPE html>
<!-- :: START INDEX ORDER PAGE -->
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./src/output.css">
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <title>KAINSTEIN | Start Order</title>
</head>

<body class="min-h-screen w-full bg-background text-dark">
    <!-- :: START LOGIN BUTTON -->
    <button id="loginOpen" type="button"
        class="fixed right-5 top-5 z-40 inline-flex items-center gap-2 rounded-xl border border-border bg-inner-card px-4 py-2.5 text-sm font-semibold text-dark shadow-sm transition hover:border-primary hover:text-primary focus:outline-none focus:ring-4 focus:ring-primary/15"
        aria-haspopup="dialog" aria-controls="loginModal">
        <i class="bx bx-user-circle text-xl" aria-hidden="true"></i>
        <span>Log in</span>
    </button>
    <!-- :: END LOGIN BUTTON -->

    <main class="h-screen flex w-full max-w-350 mx-auto justify-center items-center">
        <section
            class="w-full border border-border h-full max-h-160 md:max-h-170 lg:max-h-180 flex rounded-3xl overflow-hidden">
            <div class=" max-w-[50%] w-full overflow-hidden  hidden md:flex">
                <!-- LEFT SIDE START --> 
                <div class="relative overflow-hidden h-full w-full">
                    <img src="./assets/images/food.jpg" alt="Kainstein Food"
                        class="absolute inset-0 h-full w-full object-cover">

                    <div class="absolute inset-0 bg-black/30"></div>

                    <div class="relative z-10 flex flex-col justify-end p-6 text-white">
                        <h2 class="text-5xl font-black tracking-widest uppercase">Kainstein</h2>
                        <p>Sulit sa bulsa, halimaw sa sarap.</p>
                    </div>
                </div>
                <!-- LEFT SIDE END -->
            </div>

            <!-- RIGHT SIDE START -->
            <form method="post" class=" h-full w-full md:max-w-[50%] flex flex-col justify-between bg-background">
                <div class="flex w-full flex-col px-8 py-8 md:px-12 md:py-10">
                    <span class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-secondary">
                        Welcome to Kainstein
                    </span>
                    <h1 class="text-3xl font-bold leading-tight text-dark md:text-4xl lg:text-5xl">
                        How would you like to order?
                    </h1>
                    <!-- :: START ORDER PLACED MESSAGE -->
                    <?php if (($_GET['order'] ?? '') === 'placed'): ?>
                        <p role="status" class="mt-4 rounded-xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm font-medium text-primary">
                            Your order has been placed. Thank you, and enjoy your meal!
                        </p>
                    <?php endif; ?>
                    <!-- :: END ORDER PLACED MESSAGE -->
                    <p class="mt-3 text-base text-tertiary md:text-lg">
                        Choose your dining experience.
                    </p>

                    <?php if ($error): ?>
                        <p class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
                            <?= $error ?>
                        </p>
                    <?php endif; ?>

                    <div class="mt-8 grid grid-cols-2 items-stretch gap-4">
                        <label
                            class="has-checked:border-primary has-checked:bg-outer-card group flex min-h-52 cursor-pointer flex-col rounded-2xl border border-border bg-inner-card p-5 transition duration-200 hover:border-primary-hover hover:shadow-md focus-within:ring-2 focus-within:ring-primary/25">
                            <span class="flex w-full items-start justify-between">
                                <span
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-outer-card text-primary"
                                    aria-hidden="true">
                                    <i class="bx bx-bowl-hot text-3xl"></i>
                                </span>
                                <!-- :: START RESTORE CURRENT ORDER TYPE -->
                                <input type="radio" name="order_type" value="dine_in"
                                    <?= ($_SESSION['order_type'] ?? '') === 'dine_in' ? 'checked' : '' ?> required
                                    class="mt-1 h-4 w-4 accent-primary focus:ring-primary">
                                <!-- :: END RESTORE CURRENT ORDER TYPE -->
                            </span>
                            <span class="mt-6 block text-xl font-semibold text-dark">Dine in</span>
                            <span class="mt-2 block text-sm leading-relaxed text-tertiary">Enjoy your meal at our
                                restaurant.</span>
                            <span class="mt-auto flex items-center gap-2 pt-5 text-sm font-semibold text-primary">
                                Choose dine in <span aria-hidden="true">→</span>
                            </span>
                        </label>
                        <label
                            class="has-checked:border-primary has-checked:bg-outer-card group flex min-h-52 cursor-pointer flex-col rounded-2xl border border-border bg-inner-card p-5 transition duration-200 hover:border-primary-hover hover:shadow-md focus-within:ring-2 focus-within:ring-primary/25">
                            <span class="flex w-full items-start justify-between">
                                <span
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-outer-card text-primary"
                                    aria-hidden="true">
                                    <i class="bx bx-takeaway text-2xl"></i>
                                </span>
                                <!-- :: START RESTORE CURRENT ORDER TYPE -->
                                <input type="radio" name="order_type" value="take_out"
                                    <?= ($_SESSION['order_type'] ?? '') === 'take_out' ? 'checked' : '' ?> required
                                    class="mt-1 h-4 w-4 accent-primary focus:ring-primary">
                                <!-- :: END RESTORE CURRENT ORDER TYPE -->
                            </span>
                            <span class="mt-6 block text-xl font-semibold text-dark">Take out</span>
                            <span class="mt-2 block text-sm leading-relaxed text-tertiary">Take your order with you.
                            </span>
                            <span class="mt-auto flex items-center gap-2 pt-5 text-sm font-semibold text-primary">
                                Choose take out <span aria-hidden="true">→</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="px-8 pb-8 md:px-12 md:pb-10">
                    <button type="submit"
                        class="w-full rounded-xl bg-primary px-6 py-4 font-semibold text-light transition hover:bg-primary-hover focus:outline-none focus:ring-4 focus:ring-primary/25">
                        Continue
                        <span class="ml-2" aria-hidden="true">→</span>
                    </button>
                    <p class="mt-4 text-center text-sm text-tertiary">
                        Freshly prepared, just the way you like it.
                    </p>
                </div>
            </form>
        </section>
    </main>

    <!-- :: START LOGIN MODAL -->
    <div id="loginModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-dark/50 p-4 backdrop-blur-sm"
        aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
        <div class="my-auto w-full max-w-md rounded-2xl border border-border bg-inner-card p-6 shadow-2xl sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-secondary">Kainstein account</p>
                    <h2 id="loginTitle" class="mt-2 font-playfair text-2xl font-bold text-dark">Welcome back</h2>
                    <p class="mt-2 text-sm text-tertiary">Log in to continue to your account.</p>
                </div>
                <button id="loginClose" type="button" aria-label="Close login dialog"
                    class="grid size-10 shrink-0 place-items-center rounded-full bg-outer-card text-xl text-dark transition hover:bg-background-hover">
                    <i class="bx bx-x" aria-hidden="true"></i>
                </button>
            </div>

            <?php if ($loginFailed): ?>
                <p role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    <?= ($_GET['login'] ?? '') === 'unavailable' ? 'Login is temporarily unavailable.' : 'Email or password is incorrect.' ?>
                </p>
            <?php endif; ?>

            <form method="post" action="login.php" class="mt-6 space-y-4">
                <div>
                    <label for="loginEmail" class="mb-2 block text-sm font-semibold text-dark">Email</label>
                    <input id="loginEmail" name="email" type="email" autocomplete="email" placeholder="you@example.com" required
                        class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm text-dark outline-none transition placeholder:text-tertiary/70 focus:border-primary focus:ring-4 focus:ring-primary/10">
                </div>
                <div>
                    <label for="loginPassword" class="mb-2 block text-sm font-semibold text-dark">Password</label>
                    <input id="loginPassword" name="password" type="password" autocomplete="current-password" required
                        placeholder="Enter your password"
                        class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm text-dark outline-none transition placeholder:text-tertiary/70 focus:border-primary focus:ring-4 focus:ring-primary/10">
                </div>
                <button type="submit"
                    class="w-full rounded-xl bg-primary px-5 py-3.5 text-sm font-bold text-light transition hover:bg-primary-hover focus:outline-none focus:ring-4 focus:ring-primary/20">
                    Log in
                </button>
            </form>
        </div>
    </div>
    <!-- :: END LOGIN MODAL -->

    <!-- :: START LOGIN MODAL CONTROLS -->
    <script>
        const loginModal = document.getElementById('loginModal');
        const loginOpen = document.getElementById('loginOpen');
        const loginClose = document.getElementById('loginClose');

        function openLoginModal() {
            loginModal.classList.remove('hidden');
            loginModal.classList.add('flex');
            loginModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            document.getElementById('loginEmail').focus();
        }

        function closeLoginModal() {
            loginModal.classList.add('hidden');
            loginModal.classList.remove('flex');
            loginModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            loginOpen.focus();
        }

        loginOpen.addEventListener('click', openLoginModal);
        loginClose.addEventListener('click', closeLoginModal);
        loginModal.addEventListener('click', (event) => {
            if (event.target === loginModal) closeLoginModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !loginModal.classList.contains('hidden')) {
                closeLoginModal();
            }
        });
        <?php if ($loginFailed): ?>
            openLoginModal();
        <?php endif; ?>
    </script>
    <!-- :: END LOGIN MODAL CONTROLS -->
</body>

</html>
<!-- :: END INDEX ORDER PAGE -->
