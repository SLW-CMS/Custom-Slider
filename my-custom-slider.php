<?php
/**
 * Plugin Name: My Custom Slider
 * Plugin URI:  https://rootali.net/
 * Description: Kategorilere göre 3 farklı slider (ana, orta, sağ) - Bootstrap 5 ile responsive tasarım. Sayfanız ile mükemmel bir uyum ve herşeyden önemlisi ücretsiz :)
 * Version:     1.4
 * Author:      Ali Çömez
 * License:     GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Doğrudan erişimi engelle
}

class MyCustomSlider {
    
    private $option_name = 'my_custom_slider_settings';

    public function __construct() {
        // Admin menü & ayar kayıtları
        add_action('admin_menu', [ $this, 'create_admin_menu' ]);
        add_action('admin_init', [ $this, 'register_settings' ]);

        // Front-end'e Bootstrap dahil etme
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_bootstrap' ]);

        // Kısa kod
        add_shortcode('my_custom_slider', [ $this, 'render_slider_shortcode' ]);
    }

    /**
     * Bootstrap 5'i CDN'den çekme (ön yüz - front-end)
     */
    public function enqueue_bootstrap() {
        if ( ! is_admin() ) {
            // CSS
            wp_enqueue_style(
                'bootstrap-css',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                [],
                '5.3.0'
            );
            // JS
            wp_enqueue_script(
                'bootstrap-js',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                ['jquery'],
                '5.3.0',
                true
            );
        }
    }

    /**
     * Admin menüde ayar sayfası oluştur
     */
    public function create_admin_menu() {
        add_menu_page(
            'My Custom Slider Ayarları',
            'My Slider',
            'manage_options',
            'my-custom-slider',
            [ $this, 'settings_page_callback' ],
            'dashicons-images-alt2'
        );
    }

    /**
     * Ayar alanlarını kaydet
     */
    public function register_settings() {
        register_setting($this->option_name, $this->option_name);

        // Ana Slider Ayarları
        add_settings_section('main_slider_section', 'Ana Slider Ayarları', null, 'my-custom-slider');
        add_settings_field(
            'main_slider_categories',
            'Ana Slider Kategorileri',
            [ $this, 'field_main_slider_categories_callback' ],
            'my-custom-slider',
            'main_slider_section'
        );
        add_settings_field(
            'main_slider_post_count',
            'Ana Slider Haber Sayısı',
            [ $this, 'field_main_slider_post_count_callback' ],
            'my-custom-slider',
            'main_slider_section'
        );
        add_settings_field(
            'main_slider_order',
            'Ana Slider Sıralama',
            [ $this, 'field_main_slider_order_callback' ],
            'my-custom-slider',
            'main_slider_section'
        );

        // Ek ayarlar: Başlık gösterilsin mi, özet uzunluğu
        add_settings_field(
            'main_slider_show_title',
            'Slaytta Başlık Görünsün mü?',
            [ $this, 'field_main_slider_show_title_callback' ],
            'my-custom-slider',
            'main_slider_section'
        );
        add_settings_field(
            'main_slider_excerpt_length',
            'Slayt Özet Uzunluğu (Karakter)',
            [ $this, 'field_main_slider_excerpt_length_callback' ],
            'my-custom-slider',
            'main_slider_section'
        );

        // Orta (ikili) Slider Ayarları
        add_settings_section('middle_slider_section', 'Orta (İkili) Slider Ayarları', null, 'my-custom-slider');
        add_settings_field(
            'middle_slider_categories',
            'Orta Slider Kategorileri',
            [ $this, 'field_middle_slider_categories_callback' ],
            'my-custom-slider',
            'middle_slider_section'
        );
        add_settings_field(
            'middle_slider_post_count',
            'Orta Slider Haber Sayısı',
            [ $this, 'field_middle_slider_post_count_callback' ],
            'my-custom-slider',
            'middle_slider_section'
        );

        // Sağ (metin) Slider Ayarları
        add_settings_section('right_slider_section', 'Sağ (Metin) Slider Ayarları', null, 'my-custom-slider');
        add_settings_field(
            'right_slider_categories',
            'Sağ Slider Kategorileri',
            [ $this, 'field_right_slider_categories_callback' ],
            'my-custom-slider',
            'right_slider_section'
        );
        add_settings_field(
            'right_slider_post_count',
            'Sağ Slider Haber Sayısı',
            [ $this, 'field_right_slider_post_count_callback' ],
            'my-custom-slider',
            'right_slider_section'
        );
    }

    /**
     * Ayarlar sayfası şablonu
     */
    public function settings_page_callback() {
        ?>
        <div class="wrap">
            <h1>My Custom Slider Ayarları</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_name);
                do_settings_sections('my-custom-slider');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Ana Slider Kategorileri
     */
    public function field_main_slider_categories_callback() {
        $options = get_option($this->option_name);
        $selected_cats = isset($options['main_slider_categories']) ? $options['main_slider_categories'] : [];

        $categories = get_categories(['hide_empty' => false]);
        ?>
        <select name="<?php echo $this->option_name; ?>[main_slider_categories][]" multiple style="height:100px;">
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat->term_id; ?>"
                    <?php echo in_array($cat->term_id, $selected_cats) ? 'selected' : ''; ?>>
                    <?php echo $cat->name; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">Birden fazla kategori seçebilirsiniz.</p>
        <?php
    }

    /**
     * Ana Slider Haber Sayısı
     */
    public function field_main_slider_post_count_callback() {
        $options = get_option($this->option_name);
        $count = isset($options['main_slider_post_count']) ? $options['main_slider_post_count'] : 5;
        ?>
        <input type="number" name="<?php echo $this->option_name; ?>[main_slider_post_count]" 
               value="<?php echo esc_attr($count); ?>" min="1" max="20" />
        <?php
    }

    /**
     * Ana Slider Sıralama (Tarih / Rastgele)
     */
    public function field_main_slider_order_callback() {
        $options = get_option($this->option_name);
        $order_type = isset($options['main_slider_order']) ? $options['main_slider_order'] : 'date';
        ?>
        <select name="<?php echo $this->option_name; ?>[main_slider_order]">
            <option value="date" <?php selected($order_type, 'date'); ?>>Tarihe göre</option>
            <option value="rand" <?php selected($order_type, 'rand'); ?>>Rastgele</option>
        </select>
        <?php
    }

    /**
     * Slaytta Başlık Görünsün mü? (Yes/No)
     */
    public function field_main_slider_show_title_callback() {
        $options = get_option($this->option_name);
        $show_title = isset($options['main_slider_show_title']) ? $options['main_slider_show_title'] : 'yes';
        ?>
        <select name="<?php echo $this->option_name; ?>[main_slider_show_title]">
            <option value="yes" <?php selected($show_title, 'yes'); ?>>Evet</option>
            <option value="no" <?php selected($show_title, 'no'); ?>>Hayır</option>
        </select>
        <p class="description">Başlık fotoğrafın altında gözüksün mü?</p>
        <?php
    }

    /**
     * Slayttaki Özet Uzunluğu (karakter)
     */
    public function field_main_slider_excerpt_length_callback() {
        $options = get_option($this->option_name);
        $excerpt_len = isset($options['main_slider_excerpt_length']) ? intval($options['main_slider_excerpt_length']) : 0;
        ?>
        <input type="number" name="<?php echo $this->option_name; ?>[main_slider_excerpt_length]" 
               value="<?php echo esc_attr($excerpt_len); ?>" min="0" max="300" />
        <p class="description">0 yaparsanız özet gösterilmez. Örn: 25 (25 karakter).</p>
        <?php
    }

    /**
     * Orta (İkili) Slider Kategorileri
     */
    public function field_middle_slider_categories_callback() {
        $options = get_option($this->option_name);
        $selected_cats = isset($options['middle_slider_categories']) ? $options['middle_slider_categories'] : [];

        $categories = get_categories(['hide_empty' => false]);
        ?>
        <select name="<?php echo $this->option_name; ?>[middle_slider_categories][]" multiple style="height:100px;">
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat->term_id; ?>"
                    <?php echo in_array($cat->term_id, $selected_cats) ? 'selected' : ''; ?>>
                    <?php echo $cat->name; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Orta (İkili) Slider Haber Sayısı
     */
    public function field_middle_slider_post_count_callback() {
        $options = get_option($this->option_name);
        $count = isset($options['middle_slider_post_count']) ? $options['middle_slider_post_count'] : 2;
        ?>
        <input type="number" name="<?php echo $this->option_name; ?>[middle_slider_post_count]" 
               value="<?php echo esc_attr($count); ?>" min="1" max="10" />
        <?php
    }

    /**
     * Sağ (Metin) Slider Kategorileri
     */
    public function field_right_slider_categories_callback() {
        $options = get_option($this->option_name);
        $selected_cats = isset($options['right_slider_categories']) ? $options['right_slider_categories'] : [];

        $categories = get_categories(['hide_empty' => false]);
        ?>
        <select name="<?php echo $this->option_name; ?>[right_slider_categories][]" multiple style="height:100px;">
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat->term_id; ?>"
                    <?php echo in_array($cat->term_id, $selected_cats) ? 'selected' : ''; ?>>
                    <?php echo $cat->name; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Sağ (Metin) Slider Haber Sayısı
     */
    public function field_right_slider_post_count_callback() {
        $options = get_option($this->option_name);
        $count = isset($options['right_slider_post_count']) ? $options['right_slider_post_count'] : 5;
        ?>
        <input type="number" name="<?php echo $this->option_name; ?>[right_slider_post_count]" 
               value="<?php echo esc_attr($count); ?>" min="1" max="20" />
        <?php
    }

    /**
     * Yardımcı fonksiyon: ilgili kategorilerden postları çek
     */
    private function get_slider_posts($category_ids, $count, $order_type='date') {
        $args = [
            'post_type'      => 'post',
            'posts_per_page' => $count,
            'post_status'    => 'publish',
        ];

        if (!empty($category_ids)) {
            $args['category__in'] = $category_ids;
        }

        if ($order_type === 'rand') {
            $args['orderby'] = 'rand';
        } else {
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
        }

        return get_posts($args);
    }

    /**
     * Kısa kodun çıktı tasarımı (Bootstrap + Son İstekler)
     * - Dikey sol pagination (hover ile slayt değişimi)
     * - Başlık h2 + alt özet (karaktere göre)
     * - Sağ slider'da 50x30 resim
     * - Alt bant solda pagination'a yer bırakarak konumlanır (left:50px)
     */
    public function render_slider_shortcode($atts) {
        // Ayarları çek
        $options = get_option($this->option_name);

        // Ana slider postları
        $main_slider_posts = $this->get_slider_posts(
            isset($options['main_slider_categories']) ? $options['main_slider_categories'] : [],
            isset($options['main_slider_post_count']) ? intval($options['main_slider_post_count']) : 5,
            isset($options['main_slider_order']) ? $options['main_slider_order'] : 'date'
        );

        // Ayarlardan başlık ve özet bilgilerini al
        $show_title  = isset($options['main_slider_show_title']) ? $options['main_slider_show_title'] : 'yes';
        $excerpt_len = isset($options['main_slider_excerpt_length']) ? intval($options['main_slider_excerpt_length']) : 0;

        // Orta slider postları
        $middle_slider_posts = $this->get_slider_posts(
            isset($options['middle_slider_categories']) ? $options['middle_slider_categories'] : [],
            isset($options['middle_slider_post_count']) ? intval($options['middle_slider_post_count']) : 2,
            'date'
        );

        // Sağ slider postları
        $right_slider_posts = $this->get_slider_posts(
            isset($options['right_slider_categories']) ? $options['right_slider_categories'] : [],
            isset($options['right_slider_post_count']) ? intval($options['right_slider_post_count']) : 5,
            'date'
        );

        ob_start();
        ?>
        <div class="container my-4">
          <div class="row">
            <!-- ANA SLIDER -->
            <div class="col-12 col-md-6 position-relative mb-3 mb-md-0"
                 style="max-height:450px; overflow:hidden; border:1px solid #ccc;">
              
              <!-- Slider yüksekliği -->
              <div class="position-relative w-100 h-100" style="min-height:450px;">
                <?php 
                // Ana slaytları döngüyle oluştur
                $i=0; 
                foreach($main_slider_posts as $post):
                  $slide_display = ($i===0) ? 'block' : 'none';
                  $title     = get_the_title($post);
                  $permalink = get_permalink($post);
                  $content   = wp_strip_all_tags($post->post_content); // saf metin

                  // Özet metin (excerpt_len karakter)
                  $excerpt_text = '';
                  if ($excerpt_len > 0) {
                      $excerpt_text = mb_substr($content, 0, $excerpt_len) . '...';
                  }

                  $thumbnail_url = has_post_thumbnail($post)
                                   ? get_the_post_thumbnail_url($post, 'large')
                                   : 'https://via.placeholder.com/800x450?text=No+Image';
                ?>
                  <div class="slide position-absolute top-0 start-0 w-100 h-100"
                       style="display:<?php echo $slide_display; ?>;">
                    <a href="<?php echo esc_url($permalink); ?>"
                       class="d-block w-100 h-100 text-white position-relative">
                      <!-- Fotoğraf kaplama -->
                      <img src="<?php echo esc_url($thumbnail_url); ?>"
                           alt="<?php echo esc_attr($title); ?>"
                           class="img-fluid w-100 h-100"
                           style="object-fit:cover;" />

                      <!-- Başlık + özet bantı altta;
                           left:50px -> soldaki pagination'ın üstüne binmiyor -->
                      <div class="position-absolute bottom-0 p-2"
                           style="
                             left:50px; right:0;
                             background:rgba(0,0,0,0.4);
                             z-index:20;
                           ">
                        <?php if ($show_title === 'yes'): ?>
                          <h2 class="text-light mb-1" style="font-size:1.25rem;">
                            <?php echo esc_html($title); ?>
                          </h2>
                        <?php endif; ?>
                        <?php if ($excerpt_len > 0): ?>
                          <p class="text-light" style="font-size:0.9rem;">
                            <?php echo esc_html($excerpt_text); ?>
                          </p>
                        <?php endif; ?>
                      </div>
                    </a>
                  </div>
                <?php 
                  $i++;
                endforeach; 
                ?>

                <!-- Sol kenarda dikey pagination (eğer 2+ haber varsa) -->
                <?php if (count($main_slider_posts) > 1): ?>
                  <ul class="pagination pagination-sm flex-column m-0 p-0
                             position-absolute top-50 start-0 translate-middle-y"
                      style="z-index:10; background:rgba(255,255,255,0.6); border-radius:0 4px 4px 0;">
                    <?php
                    $j=0;
                    foreach($main_slider_posts as $post):
                      $active_class = ($j===0) ? 'active' : '';
                    ?>
                      <li class="page-item <?php echo $active_class; ?>">
                        <a href="javascript:void(0);" class="page-link slider-number"
                           data-index="<?php echo $j; ?>"
                           style="border:none;">
                          <?php echo ($j+1); ?>
                        </a>
                      </li>
                    <?php 
                      $j++;
                    endforeach;
                    ?>
                  </ul>
                <?php endif; ?>

              </div><!-- position-relative w-100 h-100 -->
            </div><!-- col-12 col-md-6 -->

            <!-- ORTA SLIDER (d-none d-md-block => küçük ekranda gizli, orta+ ekranda col-md-3) -->
            <div class="d-none d-md-block col-md-3"
                 style="max-height:450px; overflow:auto; border:1px solid #ccc;">
              <div class="p-2">
                <?php foreach($middle_slider_posts as $post): ?>
                  <?php 
                    $link  = get_permalink($post);
                    $title = get_the_title($post);
                    $thumb = has_post_thumbnail($post)
                             ? get_the_post_thumbnail_url($post, 'medium')
                             : 'https://via.placeholder.com/400x300?text=No+Image'; 
                  ?>
                  <div class="mb-3">
                    <a href="<?php echo esc_url($link); ?>" class="text-decoration-none text-dark">
                      <img src="<?php echo esc_url($thumb); ?>"
                           alt="<?php echo esc_attr($title); ?>"
                           class="img-fluid mb-2" />
                      <h4 class="h6"><?php echo esc_html($title); ?></h4>
                      <p class="text-muted" style="font-size:0.875rem;">
                        <?php echo wp_trim_words($post->post_content, 15); ?>
                      </p>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- SAĞ SLIDER (d-none d-md-block => küçük ekranda gizli, orta+ ekranda col-md-3) -->
            <div class="d-none d-md-block col-md-3"
                 style="max-height:450px; overflow:auto; border:1px solid #ccc;">
              <div class="p-2">
                <ul class="list-unstyled">
                  <?php foreach($right_slider_posts as $post): ?>
                    <li class="mb-3 d-flex align-items-center">
                      <?php 
                        // 50x30 resim
                        if (has_post_thumbnail($post)) {
                          echo get_the_post_thumbnail($post, 'thumbnail', [
                            'class' => 'me-2',
                            'style' => 'width:50px;height:30px;object-fit:cover;'
                          ]);
                        } else {
                          // placeholder
                          echo '<img src="https://via.placeholder.com/50x30?text=No+Image" 
                                     class="me-2" 
                                     style="width:50px;height:30px;object-fit:cover;" />';
                        }
                      ?>
                      <a href="<?php echo esc_url(get_permalink($post)); ?>"
                         class="text-decoration-none text-dark">
                        <?php echo esc_html(get_the_title($post)); ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>

          </div><!-- row -->
        </div><!-- container -->

        <!-- JS: Sol kenardaki dikey pagination (HOVER ile slide değişimi) -->
        <script>
        (function(){
            const mainSlider = document.querySelector('.col-md-6.position-relative');
            if(!mainSlider) return;

            const slides = mainSlider.querySelectorAll('.slide');
            const pageLinks = mainSlider.querySelectorAll('.page-link.slider-number');

            // HOVER ile
            pageLinks.forEach(link => {
                link.addEventListener('mouseover', function(e){
                    // Tüm slaytları gizle
                    slides.forEach(s => s.style.display = 'none');
                    // Tüm pagination item'larından .active kaldır
                    pageLinks.forEach(pl => {
                        pl.closest('.page-item').classList.remove('active');
                    });

                    // Index'i al
                    const index = parseInt(this.getAttribute('data-index')) || 0;
                    // İlgili slaytı göster
                    slides[index].style.display = 'block';
                    // Bu linkin li'sine .active ekle
                    this.closest('.page-item').classList.add('active');
                });
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}

// Eklentiyi başlat
new MyCustomSlider();
