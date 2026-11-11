<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Команда проекта и распределение задач';
$pageDescription = 'Информация о команде разработчиков учебного проекта «РемонтПро» и распределении задач по ролям.';
$activeMenu = 'team';

$team = [
    ['name' => 'Александр Кузнецов', 'role' => 'Руководитель проекта, backend', 'color' => '#e8a33d', 'hours' => 38, 'focus' => 'Архитектура, авторизация, роли, админ-панель'],
    ['name' => 'Марина Соколова', 'role' => 'Frontend-разработчик', 'color' => '#4d9d8a', 'hours' => 34, 'focus' => 'Вёрстка, адаптивность, слайдер, формы'],
    ['name' => 'Дмитрий Орлов', 'role' => 'База данных и безопасность', 'color' => '#6f7fd6', 'hours' => 30, 'focus' => 'Схема SQLite, запросы, CSRF и защита форм'],
    ['name' => 'Елена Смирнова', 'role' => 'UI/UX-дизайнер', 'color' => '#c76b8e', 'hours' => 28, 'focus' => 'Дизайн-система, прототипы, иллюстрации'],
    ['name' => 'Игорь Васильев', 'role' => 'Тестировщик, документация', 'color' => '#8a9a5b', 'hours' => 24, 'focus' => 'Тест-кейсы, проверка ролей, README'],
];

$tasks = [
    ['task' => 'Проектирование структуры сайта и базы данных', 'member' => 'Александр Кузнецов', 'tag' => 'Архитектура', 'hours' => 8, 'status' => 'Выполнено'],
    ['task' => 'Реализация конфигурации, сессий и подключения SQLite', 'member' => 'Александр Кузнецов', 'tag' => 'Backend', 'hours' => 6, 'status' => 'Выполнено'],
    ['task' => 'Авторизация, регистрация, хеширование паролей', 'member' => 'Александр Кузнецов', 'tag' => 'Безопасность', 'hours' => 8, 'status' => 'Выполнено'],
    ['task' => 'Контроль доступа по ролям (администратор, менеджер, клиент)', 'member' => 'Александр Кузнецов', 'tag' => 'Безопасность', 'hours' => 6, 'status' => 'Выполнено'],
    ['task' => 'Личный кабинет: профиль, история заявок, статусы', 'member' => 'Александр Кузнецов', 'tag' => 'Backend', 'hours' => 10, 'status' => 'Выполнено'],
    ['task' => 'Дизайн-система: цвета, типографика, компоненты', 'member' => 'Елена Смирнова', 'tag' => 'Дизайн', 'hours' => 12, 'status' => 'Выполнено'],
    ['task' => 'Векторные иллюстрации интерьеров и иконки', 'member' => 'Елена Смирнова', 'tag' => 'Дизайн', 'hours' => 8, 'status' => 'Выполнено'],
    ['task' => 'Адаптивные макеты для мобильных устройств', 'member' => 'Елена Смирнова', 'tag' => 'Дизайн', 'hours' => 8, 'status' => 'Выполнено'],
    ['task' => 'Вёрстка главной страницы и hero-слайдера', 'member' => 'Марина Соколова', 'tag' => 'Frontend', 'hours' => 10, 'status' => 'Выполнено'],
    ['task' => 'Вёрстка внутренних страниц и портфолио', 'member' => 'Марина Соколова', 'tag' => 'Frontend', 'hours' => 12, 'status' => 'Выполнено'],
    ['task' => 'Формы заявки, отзыва и обратной связи', 'member' => 'Марина Соколова', 'tag' => 'Frontend', 'hours' => 6, 'status' => 'Выполнено'],
    ['task' => 'Клиентская логика фильтров, меню и слайдера', 'member' => 'Марина Соколова', 'tag' => 'Frontend', 'hours' => 6, 'status' => 'Выполнено'],
    ['task' => 'Схема БД: пользователи, услуги, проекты, акции, отзывы, заявки', 'member' => 'Дмитрий Орлов', 'tag' => 'База данных', 'hours' => 8, 'status' => 'Выполнено'],
    ['task' => 'Демонстрационные данные и сиды', 'member' => 'Дмитрий Орлов', 'tag' => 'База данных', 'hours' => 4, 'status' => 'Выполнено'],
    ['task' => 'CSRF-токены, honeypot и ограничение частоты форм', 'member' => 'Дмитрий Орлов', 'tag' => 'Безопасность', 'hours' => 6, 'status' => 'Выполнено'],
    ['task' => 'Подготовка SQL-запросов и защита от инъекций', 'member' => 'Дмитрий Орлов', 'tag' => 'База данных', 'hours' => 6, 'status' => 'Выполнено'],
    ['task' => 'Кеширование данных и статических ресурсов', 'member' => 'Дмитрий Орлов', 'tag' => 'Производительность', 'hours' => 4, 'status' => 'Выполнено'],
    ['task' => 'Тестирование сценариев по ролям', 'member' => 'Игорь Васильев', 'tag' => 'Тестирование', 'hours' => 8, 'status' => 'Выполнено'],
    ['task' => 'Проверка адаптивности и кроссбраузерности', 'member' => 'Игорь Васильев', 'tag' => 'Тестирование', 'hours' => 6, 'status' => 'Выполнено'],
    ['task' => 'Описание проекта, README и инструкция запуска', 'member' => 'Игорь Васильев', 'tag' => 'Документация', 'hours' => 6, 'status' => 'Выполнено'],
    ['task' => 'Финальная проверка форм и сообщений об ошибках', 'member' => 'Игорь Васильев', 'tag' => 'Тестирование', 'hours' => 4, 'status' => 'Выполнено'],
];

$totalHours = array_sum(array_column($tasks, 'hours'));
$totalTasks = count($tasks);

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= url('index.php') ?>">Главная</a><span class="sep">/</span><span>Команда проекта</span>
        </nav>
        <span class="eyebrow">Учебный проект · Вариант 12</span>
        <h1>Команда и распределение задач</h1>
        <p>Сайт «Профессиональный ремонт квартир и коттеджей» разработан студенческой командой в рамках учебного задания. Сроки разработки: 02.11.2026 – 14.11.2026.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Состав команды</span>
                <h2>Пять специалистов, одна цель</h2>
                <p>Каждый участник отвечал за свой блок работ: от проектирования базы данных до финального тестирования.</p>
            </div>
        </div>
        <div class="team-grid">
            <?php foreach ($team as $member): ?>
                <div class="team-card" data-reveal>
                    <span class="avatar avatar--lg" style="background:<?= e($member['color']) ?>"><?= e(initials($member['name'])) ?></span>
                    <h3><?= e($member['name']) ?></h3>
                    <p><?= e($member['role']) ?></p>
                    <p class="mt-1" style="font-size:11.5px"><?= e($member['focus']) ?></p>
                    <span class="team-card__hours"><?= (int)$member['hours'] ?> ч</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="section-head">
            <div class="section-head__text">
                <span class="eyebrow">Распределение работ</span>
                <h2>Реестр задач команды</h2>
                <p>Всего задач: <?= $totalTasks ?> · суммарная оценка трудозатрат: <?= $totalHours ?> часов. Все задачи учебного проекта выполнены.</p>
            </div>
            <span class="badge status-completed">Проект сдан</span>
        </div>
        <div class="panel">
            <div class="panel__body panel__body--flush">
                <div class="table-wrap">
                    <table class="table tasks-table">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Задача</th>
                                <th>Исполнитель</th>
                                <th>Категория</th>
                                <th>Часы</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $i => $task): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="cell-main"><?= e($task['task']) ?></td>
                                    <td class="role-cell">
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <?php
                                            $memberColor = '#e8a33d';
                                            foreach ($team as $m) { if ($m['name'] === $task['member']) { $memberColor = $m['color']; } }
                                            ?>
                                            <span class="avatar avatar--sm" style="background:<?= e($memberColor) ?>;width:26px;height:26px;font-size:9.5px"><?= e(initials($task['member'])) ?></span>
                                            <span><?= e($task['member']) ?></span>
                                        </div>
                                    </td>
                                    <td><span class="task-tag"><?= e($task['tag']) ?></span></td>
                                    <td><?= (int)$task['hours'] ?></td>
                                    <td><span class="badge status-completed"><?= e($task['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="admin-grid admin-grid--equal">
            <div class="panel">
                <div class="panel__head"><h2>Технологический стек</h2></div>
                <div class="panel__body">
                    <ul class="check-list">
                        <li><strong>Backend:</strong> PHP 8.3+ без фреймворка, PDO</li>
                        <li><strong>База данных:</strong> SQLite с режимом WAL</li>
                        <li><strong>Frontend:</strong> HTML5, CSS3, Vanilla JavaScript</li>
                        <li><strong>Безопасность:</strong> bcrypt, CSRF, honeypot, rate limiting</li>
                        <li><strong>Производительность:</strong> файловое кеширование, ленивые изображения</li>
                    </ul>
                </div>
            </div>
            <div class="panel">
                <div class="panel__head"><h2>Роли и доступ</h2></div>
                <div class="panel__body">
                    <div class="table-wrap">
                        <table class="table table--compact">
                            <thead><tr><th>Роль</th><th>Права</th></tr></thead>
                            <tbody>
                                <tr><td><span class="badge badge-admin">Администратор</span></td><td>Полный доступ, пользователи, все разделы</td></tr>
                                <tr><td><span class="badge badge-manager">Менеджер</span></td><td>Заявки и просмотр отзывов</td></tr>
                                <tr><td><span class="badge badge-user">Клиент</span></td><td>Заявки, отзывы, личный кабинет</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-callout mt-4">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            <div>Проект выполнен в учебных целях. Демонстрационные аккаунты указаны на странице входа. Все данные вымышлены и используются только для демонстрации функциональности.</div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
