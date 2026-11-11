</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer__top">
            <div class="footer__brand">
                <a class="logo logo--light" href="<?= url('index.php') ?>">
                    <span class="logo__mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48"><rect width="48" height="48" rx="14" fill="#e8a33d"/><path d="M14 31.5V22l10-6.5 10 6.5v9.5" fill="none" stroke="#14181f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 33v-7h8v7" fill="none" stroke="#14181f" stroke-width="3" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="logo__text">
                        <strong>Ремонт<span>Про</span></strong>
                        <small>квартиры и коттеджи</small>
                    </span>
                </a>
                <p class="footer__about">Профессиональный ремонт квартир и коттеджей под ключ с 2012 года. Собственные бригады, фиксированная смета и гарантия 3 года на все работы.</p>
                <div class="footer__socials">
                    <a href="#" aria-label="Telegram"><svg viewBox="0 0 24 24"><path d="M21.9 4.3L18.6 20c-.2 1.1-.9 1.4-1.8.9l-5-3.7-2.4 2.3c-.3.3-.5.5-1 .5l.4-5 9.1-8.2c.4-.4-.1-.6-.6-.2L6.1 13.3 1.2 11.8c-1.1-.3-1.1-1.1.2-1.6l19-7.3c.9-.3 1.7.2 1.5 1.4z"/></svg></a>
                    <a href="#" aria-label="VK"><svg viewBox="0 0 24 24"><path d="M12.8 17.5c-5.4 0-8.7-3.8-8.9-10h2.8c.1 4.6 2.2 6.5 3.7 6.9V7.5h2.6v3.9c1.5-.2 3.1-1.9 3.6-3.9h2.6c-.4 2.5-2 4.2-3.2 4.9 1.2.6 3 2.1 3.7 5.1h-2.9c-.5-1.8-1.9-3.2-3.8-3.4v3.4h-.2z"/></svg></a>
                    <a href="#" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M23 7.2s-.2-1.6-.9-2.3c-.8-.9-1.7-.9-2.1-1C17.2 3.6 12 3.6 12 3.6s-5.2 0-8 .3c-.4.1-1.3.1-2.1 1-.7.7-.9 2.3-.9 2.3S.8 9.1.8 11v1.8c0 1.9.2 3.8.2 3.8s.2 1.6.9 2.3c.8.9 1.9.9 2.4 1 1.7.2 7.7.3 7.7.3s5.2 0 8-.3c.4-.1 1.3-.1 2.1-1 .7-.7.9-2.3.9-2.3s.2-1.9.2-3.8V11c0-1.9-.2-3.8-.2-3.8zM9.9 15.2V8.4l6 3.4-6 3.4z"/></svg></a>
                </div>
            </div>

            <div class="footer__col">
                <h3>Компания</h3>
                <a href="<?= url('about.php') ?>">О компании</a>
                <a href="<?= url('services.php') ?>">Услуги</a>
                <a href="<?= url('portfolio.php') ?>">Портфолио</a>
                <a href="<?= url('reviews.php') ?>">Отзывы</a>
                <a href="<?= url('team.php') ?>">Команда проекта</a>
            </div>

            <div class="footer__col">
                <h3>Клиентам</h3>
                <a href="<?= url('promos.php') ?>">Акции и скидки</a>
                <a href="<?= url('request.php') ?>">Заявка на ремонт</a>
                <a href="<?= url('cabinet.php') ?>">Личный кабинет</a>
                <a href="<?= url('contacts.php') ?>">Контакты</a>
                <?php if (current_user() && in_array(current_user()['role'], ['admin', 'manager'], true)): ?>
                    <a href="<?= url('admin/index.php') ?>">Админ-панель</a>
                <?php else: ?>
                    <a href="<?= url('login.php') ?>">Вход для сотрудников</a>
                <?php endif; ?>
            </div>

            <div class="footer__col footer__col--contacts">
                <h3>Контакты</h3>
                <a class="footer__phone" href="tel:+74951204500">+7 (495) 120-45-00</a>
                <a href="mailto:info@remontpro.ru">info@remontpro.ru</a>
                <p>Москва, ул. Большая Новодмитровская, 23, стр. 2<br>офис 214</p>
                <p>Пн–Вс: 9:00–21:00</p>
            </div>
        </div>

        <div class="footer__legal">
            <p>© <?= date('Y') ?> ООО «РемонтПро». ИНН 7701234567. ОГРН 1127746000000. Все права защищены.</p>
            <div class="footer__legal-links">
                <a href="<?= url('about.php') ?>#documents">Политика конфиденциальности</a>
                <span>Сайт разработан командой студентов в учебных целях</span>
                <a href="<?= url('team.php') ?>">Распределение задач по команде</a>
            </div>
        </div>
    </div>
</footer>

<button class="to-top" id="toTop" aria-label="Вернуться наверх" hidden>
    <svg viewBox="0 0 24 24"><path d="M12 5l7 7-1.4 1.4L13 8.8V19h-2V8.8L6.4 13.4 5 12l7-7z"/></svg>
</button>

<script src="<?= url('assets/js/main.js') ?>?v=13" defer></script>
</body>
</html>
