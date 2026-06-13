<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

start_app_session();

$errors = [];
$success = isset($_GET['success']);
$values = [
    'parent_name' => '',
    'child_age' => '',
    'condition' => 'Autism Spectrum Disorder',
    'country' => 'India',
    'phone' => '',
    'email' => '',
    'preferred_time' => 'morning',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = normalize_inquiry_from_post();

    if (post_value('website') !== '') {
        redirect_to('index.php?success=1');
    }

    if (!csrf_is_valid()) {
        $errors['_form'] = 'The form expired. Please try again.';
    } else {
        $errors = validate_inquiry($values);

        if ($errors === []) {
            save_inquiry($values);
            redirect_to('index.php?success=1');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consultation Inquiry - <?php echo h(APP_NAME); ?></title>
    <style>
        :root {
            --primary: #0c4f2f;
            --primary-light: #1a6b42;
            --accent: #d4af37;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --soft: #f8fbf9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: linear-gradient(135deg, #f8fbf9, #ffffff 58%, rgba(212, 175, 55, 0.10));
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.5;
        }

        .page {
            min-height: 100vh;
            padding: 32px 18px;
        }

        .shell {
            max-width: 1080px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 36px;
        }

        .brand {
            color: var(--primary);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .admin-link {
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--primary);
            font-weight: 700;
            padding: 10px 14px;
            text-decoration: none;
        }

        .hero {
            display: grid;
            gap: 28px;
            grid-template-columns: minmax(0, 0.85fr) minmax(320px, 1.15fr);
            align-items: start;
        }

        .copy {
            padding-top: 24px;
        }

        .eyebrow {
            color: var(--primary);
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }

        h1 {
            margin: 14px 0 18px;
            max-width: 620px;
            font-size: clamp(34px, 5vw, 64px);
            line-height: 1.02;
        }

        .lead {
            max-width: 580px;
            color: var(--muted);
            font-size: 18px;
        }

        .panel {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 28px 90px rgba(12, 79, 47, 0.12);
            overflow: hidden;
        }

        .panel-header {
            border-top: 6px solid var(--primary);
            border-bottom: 1px solid var(--border);
            padding: 22px 24px;
        }

        .panel-header h2 {
            margin: 0;
            font-size: 24px;
            line-height: 1.2;
        }

        .panel-header p {
            margin: 8px 0 0;
            color: var(--muted);
        }

        form {
            padding: 24px;
        }

        .grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        label {
            display: block;
            color: var(--text);
            font-size: 14px;
            font-weight: 700;
        }

        input,
        select,
        textarea {
            display: block;
            width: 100%;
            margin-top: 8px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: #fff;
            color: var(--text);
            font: inherit;
            padding: 13px 14px;
        }

        textarea {
            min-height: 118px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: 2px solid rgba(12, 79, 47, 0.16);
            outline-offset: 2px;
        }

        .full {
            grid-column: 1 / -1;
        }

        .error {
            color: #b42318;
            font-size: 13px;
            font-weight: 700;
            margin-top: 7px;
        }

        .alert {
            border-radius: 6px;
            margin-bottom: 18px;
            padding: 14px 16px;
            font-weight: 700;
        }

        .alert-success {
            background: rgba(12, 79, 47, 0.10);
            color: var(--primary);
        }

        .alert-error {
            background: rgba(180, 35, 24, 0.08);
            color: #b42318;
        }

        .honeypot {
            left: -9999px;
            position: absolute;
        }

        .actions {
            align-items: center;
            display: flex;
            gap: 14px;
            justify-content: space-between;
            margin-top: 22px;
        }

        button {
            border: 1px solid var(--primary);
            border-radius: 6px;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 800;
            min-height: 52px;
            padding: 0 22px;
        }

        button:hover {
            background: var(--primary-light);
        }

        .note {
            color: var(--muted);
            font-size: 13px;
            max-width: 320px;
        }

        @media (max-width: 860px) {
            .hero,
            .grid {
                grid-template-columns: 1fr;
            }

            .copy {
                padding-top: 0;
            }

            .actions {
                align-items: stretch;
                flex-direction: column;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="shell">
            <div class="topbar">
                <div class="brand"><?php echo h(APP_NAME); ?></div>
                <a class="admin-link" href="admin/index.php">Admin inquiry</a>
            </div>

            <section class="hero">
                <div class="copy">
                    <p class="eyebrow">Consultation inquiry</p>
                    <h1>Share the first details with the care desk.</h1>
                    <p class="lead">
                        Parents can submit the child care details here. Every entry is saved
                        securely and appears inside the admin inquiry page.
                    </p>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2>Book a consultation</h2>
                        <p>A team member will review the inquiry and contact the family.</p>
                    </div>

                    <form method="post" action="index.php" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                        <label class="honeypot">
                            Website
                            <input name="website" autocomplete="off" tabindex="-1">
                        </label>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Thank you. Your inquiry has been received.
                            </div>
                        <?php endif; ?>

                        <?php if (isset($errors['_form'])): ?>
                            <div class="alert alert-error"><?php echo h($errors['_form']); ?></div>
                        <?php endif; ?>

                        <div class="grid">
                            <label>
                                Parent name
                                <input name="parent_name" value="<?php echo h($values['parent_name']); ?>" placeholder="Full name" required>
                                <?php if (isset($errors['parent_name'])): ?><div class="error"><?php echo h($errors['parent_name']); ?></div><?php endif; ?>
                            </label>

                            <label>
                                Child age
                                <input name="child_age" type="number" min="0" max="18" value="<?php echo h($values['child_age']); ?>" required>
                                <?php if (isset($errors['child_age'])): ?><div class="error"><?php echo h($errors['child_age']); ?></div><?php endif; ?>
                            </label>

                            <label>
                                Primary concern
                                <select name="condition" required>
                                    <?php foreach (CONDITION_OPTIONS as $condition): ?>
                                        <option value="<?php echo h($condition); ?>" <?php echo $values['condition'] === $condition ? 'selected' : ''; ?>>
                                            <?php echo h($condition); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['condition'])): ?><div class="error"><?php echo h($errors['condition']); ?></div><?php endif; ?>
                            </label>

                            <label>
                                Country
                                <input name="country" value="<?php echo h($values['country']); ?>" placeholder="India" required>
                                <?php if (isset($errors['country'])): ?><div class="error"><?php echo h($errors['country']); ?></div><?php endif; ?>
                            </label>

                            <label>
                                Phone
                                <input name="phone" inputmode="tel" value="<?php echo h($values['phone']); ?>" placeholder="+917874876777" required>
                                <?php if (isset($errors['phone'])): ?><div class="error"><?php echo h($errors['phone']); ?></div><?php endif; ?>
                            </label>

                            <label>
                                Email
                                <input name="email" type="email" value="<?php echo h($values['email']); ?>" placeholder="parent@example.com" required>
                                <?php if (isset($errors['email'])): ?><div class="error"><?php echo h($errors['email']); ?></div><?php endif; ?>
                            </label>

                            <label>
                                Preferred time
                                <select name="preferred_time" required>
                                    <?php foreach (PREFERRED_TIME_OPTIONS as $time): ?>
                                        <option value="<?php echo h($time); ?>" <?php echo $values['preferred_time'] === $time ? 'selected' : ''; ?>>
                                            <?php echo h(ucfirst($time)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['preferred_time'])): ?><div class="error"><?php echo h($errors['preferred_time']); ?></div><?php endif; ?>
                            </label>

                            <label class="full">
                                Message
                                <textarea name="message" placeholder="Briefly share speech, attention, behavior, sleep, learning, therapy, or report details."><?php echo h($values['message']); ?></textarea>
                                <?php if (isset($errors['message'])): ?><div class="error"><?php echo h($errors['message']); ?></div><?php endif; ?>
                            </label>
                        </div>

                        <div class="actions">
                            <button type="submit">Start Assessment</button>
                            <p class="note">The care desk will review the request and contact the family with next steps.</p>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
