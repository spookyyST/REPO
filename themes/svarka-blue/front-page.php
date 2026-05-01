<?php
/**
 * Front page template.
 *
 * @package SvarkaBlue
 */

if (!defined('ABSPATH')) {
    exit;
}

$equipment_cards = svarka_blue_home_equipment_cards();
$partners   = svarka_blue_partner_logos();
$news_items = svarka_blue_news_items();
$hero_banner = content_url('/uploads/2026/04/Generated Image April 30, 2026 - 11_19AM.jpg');

get_header();
?>

<main class="site-main reference-home">
    <section class="reference-hero" aria-label="Профессиональная сварка" style="--hero-banner: url('<?php echo esc_url($hero_banner); ?>');">
        <button class="hero-arrow hero-arrow--prev" type="button" aria-label="Предыдущий слайд">
            <i data-lucide="chevron-left"></i>
        </button>
        <button class="hero-arrow hero-arrow--next" type="button" aria-label="Следующий слайд">
            <i data-lucide="chevron-right"></i>
        </button>
    </section>

    <section class="reference-search" aria-label="Поиск товара">
        <div class="container">
            <form class="reference-search__form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" name="s" placeholder="Введите артикул или наименование товара" value="<?php echo esc_attr(get_search_query()); ?>">
                <button type="submit">Поиск</button>
            </form>
        </div>
    </section>

    <section class="reference-partners" aria-label="Партнеры">
        <div class="reference-partners__viewport">
            <div class="reference-partners__track">
                <?php for ($loop = 0; $loop < 2; $loop++) : ?>
                    <?php foreach ($partners as $partner) : ?>
                        <span class="partner-logo">
                            <img src="<?php echo esc_url($partner['image']); ?>" alt="<?php echo esc_attr($partner['label']); ?>" loading="lazy">
                        </span>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <section class="reference-section reference-catalog" id="equipment">
        <div class="container">
            <h1 class="reference-title">КАТАЛОГ ОБОРУДОВАНИЯ HUGONG</h1>
            <div class="reference-category-grid">
                <?php foreach ($equipment_cards as $category) : ?>
                    <article class="reference-category-card">
                        <a href="<?php echo esc_url($category['url']); ?>" class="reference-category-card__link">
                            <span class="reference-category-card__image">
                                <img src="<?php echo esc_url($category['image']); ?>" alt="<?php echo esc_attr($category['title']); ?>">
                            </span>
                            <span class="reference-category-card__content">
                                <h2><?php echo esc_html($category['title']); ?></h2>
                                <span class="reference-category-card__more">
                                    Подробнее
                                    <i data-lucide="arrow-up-right"></i>
                                </span>
                            </span>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="reference-category-more">
                <a class="button button--primary reference-category-more__button" href="<?php echo esc_url(home_url('/catalog/')); ?>">
                    Показать все
                    <i data-lucide="arrow-up-right"></i>
                </a>
            </div>
        </div>
    </section>

    <section class="reference-section reference-news" id="news">
        <div class="container">
            <h2 class="reference-title">НОВОСТИ КОМПАНИИ HUGONG</h2>
            <?php
            $news_cover = isset($news_items[2]) ? $news_items[2] : $news_items[0];
            ?>
            <div class="reference-news-magazine-shell">
                <div class="reference-news-book" data-news-book>
                    <article class="reference-news-page reference-news-page--cover">
                        <img src="<?php echo esc_url($news_cover['image']); ?>" alt="<?php echo esc_attr($news_cover['title']); ?>">
                        <div class="reference-news-page__cover-copy">
                            <span>HUGONG JOURNAL</span>
                            <h3>Сварка, производство и события отрасли</h3>
                            <p><?php echo esc_html($news_cover['title']); ?></p>
                        </div>
                    </article>
                    <article class="reference-news-page reference-news-page--digest">
                        <span class="reference-news-page__kicker">Свежий выпуск</span>
                        <h3>Новости HUGONG</h3>
                        <div class="reference-news-page__grid">
                            <?php foreach ($news_items as $item) : ?>
                                <div class="reference-news-page__card">
                                    <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                                    <time><?php echo esc_html($item['date']); ?></time>
                                    <h4><?php echo esc_html($item['title']); ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                    <article class="reference-news-page reference-news-page--feature">
                        <span class="reference-news-page__kicker">Оборудование</span>
                        <h3>Решения для производственных задач</h3>
                        <img src="<?php echo esc_url($news_items[0]['image']); ?>" alt="<?php echo esc_attr($news_items[0]['title']); ?>">
                        <p>Демонстрации, выставки и реальные кейсы показывают, как оборудование HUGONG работает на производстве, в сервисе и монтажных задачах.</p>
                    </article>
                    <article class="reference-news-page reference-news-page--feature">
                        <span class="reference-news-page__kicker">Выставки</span>
                        <h3>HUGONG на профильных мероприятиях</h3>
                        <img src="<?php echo esc_url($news_items[1]['image']); ?>" alt="<?php echo esc_attr($news_items[1]['title']); ?>">
                        <p>Показываем оборудование на стендах, проводим консультации и помогаем подобрать аппараты под реальные задачи производства.</p>
                    </article>
                    <article class="reference-news-page reference-news-page--feature">
                        <span class="reference-news-page__kicker">Практика</span>
                        <h3>Демонстрации сварочного оборудования</h3>
                        <img src="<?php echo esc_url($news_items[2]['image']); ?>" alt="<?php echo esc_attr($news_items[2]['title']); ?>">
                        <p>Проводим показы оборудования, сравниваем режимы сварки и объясняем настройки для стабильной работы на объекте.</p>
                    </article>
                    <article class="reference-news-page reference-news-page--feature">
                        <span class="reference-news-page__kicker">Сервис</span>
                        <h3>Поддержка после поставки</h3>
                        <img src="<?php echo esc_url($news_items[3]['image']); ?>" alt="<?php echo esc_attr($news_items[3]['title']); ?>">
                        <p>Помогаем с запуском, расходными материалами, запчастями и техническими консультациями по оборудованию HUGONG.</p>
                    </article>
                    <article class="reference-news-page reference-news-page--cta">
                        <span class="reference-news-page__kicker">Следующий шаг</span>
                        <h3>Подберите сварочное оборудование под вашу задачу</h3>
                        <p>Расскажите о материале, толщине, режиме работы и нужном процессе. Мы предложим комплект и покажем варианты.</p>
                        <a class="reference-news-page__button" href="#request">Оставить заявку</a>
                    </article>
                </div>
                <div class="reference-news-controls">
                    <button type="button" data-news-prev aria-label="Предыдущая страница">
                        <i data-lucide="chevron-left"></i>
                    </button>
                    <button type="button" data-news-next aria-label="Следующая страница">
                        <i data-lucide="chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <?php
    $process_steps = array(
        array(
            'title' => 'Подбираем задачу',
            'desc'  => 'Разбираем металл, толщины, режим работы и требования к сварочному процессу.',
        ),
        array(
            'title' => 'Предлагаем оборудование',
            'desc'  => 'Собираем комплект HUGONG под MIG/MAG, TIG, MMA, CUT или лазерные задачи.',
        ),
        array(
            'title' => 'Показываем в работе',
            'desc'  => 'Проводим демонстрацию, объясняем настройки и помогаем сравнить варианты.',
        ),
        array(
            'title' => 'Поставляем и запускаем',
            'desc'  => 'Организуем доставку, ввод в эксплуатацию и базовое обучение операторов.',
        ),
        array(
            'title' => 'Сопровождаем сервисом',
            'desc'  => 'Поддерживаем гарантию, расходные материалы, запчасти и технические консультации.',
        ),
    );
    ?>
    <section id="process" class="reference-section process-rays">
        <div class="container">
            <div class="section-head">
                <h2>От подбора аппарата до запуска на производстве</h2>
            </div>
            <div class="process-rays__grid">
                <div class="process-rays__rail" aria-hidden="true">
                    <span class="process-rays__glow"></span>
                </div>
                <?php foreach ($process_steps as $index => $step) : ?>
                    <article class="process-rays__step <?php echo $index % 2 ? 'process-rays__step--right' : 'process-rays__step--left'; ?>" data-ray-step data-delay="<?php echo esc_attr((string) ($index * 140)); ?>">
                        <div class="process-rays__bubble">
                            <div class="process-rays__content">
                                <div class="process-rays__num" aria-hidden="true"><?php echo esc_html((string) ($index + 1)); ?></div>
                                <div class="process-rays__text">
                                    <h3><?php echo esc_html($step['title']); ?></h3>
                                    <p><?php echo esc_html($step['desc']); ?></p>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <article class="process-rays__step process-rays__step--right" data-ray-step data-delay="700">
                    <div class="process-rays__bubble">
                        <div class="process-rays__content">
                            <div class="process-rays__num" aria-hidden="true">6</div>
                            <div class="process-rays__text">
                                <a class="process-rays__cta-link" href="#request">Оставить заявку</a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="about-cinematic" id="about">
        <div class="about-bg-text" aria-hidden="true">О компании</div>
        <div class="about-wrap">
            <div class="about-left about-reveal" data-about-reveal>
                <span class="about-label">О компании</span>
                <h2>Профессиональное оборудование для сильного производства</h2>
                <p>
                    HUGONG - один из крупнейших производителей сварочного оборудования. Мы поставляем решения для бизнеса,
                    производств и сервисных центров, помогаем с подбором, запуском и дальнейшей эксплуатацией.
                </p>
                <a href="#request" class="about-link">Получить консультацию</a>
            </div>
            <div class="about-center about-reveal" data-about-reveal>
                <img src="<?php echo esc_url($equipment_cards[0]['image']); ?>" alt="Сварочное оборудование HUGONG">
            </div>
            <div class="about-right">
                <div class="stat-card about-reveal" data-about-reveal>
                    <b data-count-to="60" data-count-suffix="+">0+</b>
                    <span>лет инженерного опыта HUGONG</span>
                </div>
                <div class="stat-card about-reveal" data-about-reveal>
                    <b data-count-to="100" data-count-suffix="+">0+</b>
                    <span>стран используют оборудование</span>
                </div>
                <div class="stat-card about-reveal" data-about-reveal>
                    <b data-count-to="500" data-count-suffix="+">0+</b>
                    <span>производственных задач закрываем</span>
                </div>
                <div class="stat-card stat-card--accent about-reveal" data-about-reveal>
                    <b data-count-static="24/7">24/7</b>
                    <span>техническая поддержка и консультации</span>
                </div>
            </div>
        </div>
    </section>

    <section class="reference-section reference-text">
        <div class="container">
            <div class="reference-text__head">
                <span>Технологии HUGONG</span>
                <h2>Сварочное оборудование под разные производственные задачи</h2>
                <p>Hugong - один из крупнейших экспортеров профессионального и полупрофессионального сварочного оборудования. Мы подбираем решения для производства, сервиса, монтажа и сложных технологических процессов.</p>
            </div>
            <div class="reference-text__grid">
                <article>
                    <span>MMA</span>
                    <h3>Дуговая сварка</h3>
                    <p>Аппараты для работы с покрытым электродом по чугуну, нержавеющей и малоуглеродистой стали.</p>
                </article>
                <article>
                    <span>MIG/MAG</span>
                    <h3>Полуавтоматическая сварка</h3>
                    <p>Решения для стабильной сварки с применением инертного или активного защитного газа.</p>
                </article>
                <article>
                    <span>TIG</span>
                    <h3>Аргонодуговая сварка</h3>
                    <p>Оборудование для аккуратных, ответственных и профессиональных работ с высоким качеством шва.</p>
                </article>
                <article>
                    <span>CUT</span>
                    <h3>Плазменная резка</h3>
                    <p>Промышленные аппараты для воздушно-плазменной резки металлов в производстве и сервисе.</p>
                </article>
                <article>
                    <span>SAW</span>
                    <h3>Сварочные тракторы</h3>
                    <p>Универсальные промышленные комплексы для длинных швов и повторяемых производственных операций.</p>
                </article>
                <article>
                    <span>LASER</span>
                    <h3>Лазерная сварка</h3>
                    <p>Современное оборудование и дополнительные комплектующие для точных технологических задач.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
