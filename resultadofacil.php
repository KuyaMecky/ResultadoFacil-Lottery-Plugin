<?php
/**
 * Plugin Name: ResultadoFacil Lottery Plugin
 * Description: A comprehensive WordPress plugin for ResultadoFacil.org with lottery results, ticket verification, and content management.
 * Version: 1.0
 * Author: Michael Tallada
 * Text Domain: resultadofacil
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RESULTADOFACIL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RESULTADOFACIL_PLUGIN_PATH', plugin_dir_path(__FILE__));

class ResultadoFacilPlugin {
    
    private $redirect_url = 'https://seo813.pages.dev?agentid=Bet606';
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register shortcodes
        $this->register_shortcodes();
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('resultadofacil', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function admin_menu() {
        add_options_page(
            'ResultadoFacil Settings',
            'ResultadoFacil',
            'manage_options',
            'resultadofacil-settings',
            array($this, 'settings_page')
        );
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>ResultadoFacil Plugin Settings</h1>
            
            <div class="card">
                <h2>Available Shortcodes</h2>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Description</th>
                            <th>Suggested URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[rf_resultados_hoy]</code></td>
                            <td>Today's lottery results</td>
                            <td>/resultados-de-hoy/</td>
                        </tr>
                        <tr>
                            <td><code>[rf_verificar_boleto]</code></td>
                            <td>Ticket verification form</td>
                            <td>/verificar-boleto/</td>
                        </tr>
                        <tr>
                            <td><code>[rf_mega_sena]</code></td>
                            <td>Mega-Sena results and info</td>
                            <td>/mega-sena/</td>
                        </tr>
                        <tr>
                            <td><code>[rf_calendario_sorteos]</code></td>
                            <td>Lottery calendar</td>
                            <td>/calendario-sorteos/</td>
                        </tr>
                        <tr>
                            <td><code>[rf_como_jugar_lotofacil]</code></td>
                            <td>How to play Lotofácil guide</td>
                            <td>/como-jugar-lotofacil/</td>
                        </tr>
                        <tr>
                            <td><code>[rf_como_cobrar_premio]</code></td>
                            <td>How to claim prizes guide</td>
                            <td>/como-cobrar-premio/</td>
                        </tr>
                        <tr>
                            <td><code>[rf_noticias_loteria]</code></td>
                            <td>Lottery news</td>
                            <td>/noticias-loteria/</td>
                        </tr>
                        <tr>
                            <td><code>[rf_resultados_vivo]</code></td>
                            <td>Live results</td>
                            <td>/resultados-en-vivo/</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Auto-Create Pages</h2>
                <p>Click the button below to automatically create all pages with the appropriate shortcodes:</p>
                <form method="post" action="">
                    <?php wp_nonce_field('rf_create_pages', 'rf_nonce'); ?>
                    <input type="submit" name="create_pages" class="button-primary" value="Create All Pages">
                </form>
                <?php
                if (isset($_POST['create_pages']) && wp_verify_nonce($_POST['rf_nonce'], 'rf_create_pages')) {
                    $this->create_all_pages();
                    echo '<div class="notice notice-success"><p>Pages created successfully!</p></div>';
                }
                ?>
            </div>
            
            <div class="card">
                <h2>Settings</h2>
                <p><strong>External Link Redirect:</strong> <?php echo esc_url($this->redirect_url); ?></p>
                <p>All external links from scraped content will be redirected to this URL.</p>
            </div>
        </div>
        <?php
    }
    
    private function register_shortcodes() {
        add_shortcode('rf_resultados_hoy', array($this, 'shortcode_resultados_hoy'));
        add_shortcode('rf_verificar_boleto', array($this, 'shortcode_verificar_boleto'));
        add_shortcode('rf_mega_sena', array($this, 'shortcode_mega_sena'));
        add_shortcode('rf_calendario_sorteos', array($this, 'shortcode_calendario_sorteos'));
        add_shortcode('rf_como_jugar_lotofacil', array($this, 'shortcode_como_jugar_lotofacil'));
        add_shortcode('rf_como_cobrar_premio', array($this, 'shortcode_como_cobrar_premio'));
        add_shortcode('rf_noticias_loteria', array($this, 'shortcode_noticias_loteria'));
        add_shortcode('rf_resultados_vivo', array($this, 'shortcode_resultados_vivo'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('resultadofacil-style', RESULTADOFACIL_PLUGIN_URL . 'assets/style.css', array(), '1.0');
        wp_enqueue_script('resultadofacil-script', RESULTADOFACIL_PLUGIN_URL . 'assets/script.js', array('jquery'), '1.0', true);
        
        // Localize script for AJAX
        wp_localize_script('resultadofacil-script', 'rf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rf_ajax_nonce')
        ));
    }
    
    // Shortcode implementations
    public function shortcode_resultados_hoy($atts) {
        $content = $this->scrape_lottery_results();
        
        return '
        <div class="rf-resultados-hoy">
            <h1>Resultados de Lotería de Hoy</h1>
            <p class="rf-intro">Consulta los resultados más recientes de las principales loterías de Brasil y América Latina. Actualizamos esta página en tiempo real para que sepas al instante si eres uno de los afortunados ganadores.</p>
            
            <div class="rf-lottery-grid">
                <div class="rf-lottery-card">
                    <h3>Mega-Sena</h3>
                    <div class="rf-numbers">' . $this->generate_sample_numbers(6) . '</div>
                    <p class="rf-prize">Premio: R$ 45.000.000</p>
                </div>
                
                <div class="rf-lottery-card">
                    <h3>Lotofácil</h3>
                    <div class="rf-numbers">' . $this->generate_sample_numbers(15) . '</div>
                    <p class="rf-prize">Premio: R$ 1.500.000</p>
                </div>
                
                <div class="rf-lottery-card">
                    <h3>Quina</h3>
                    <div class="rf-numbers">' . $this->generate_sample_numbers(5) . '</div>
                    <p class="rf-prize">Premio: R$ 8.000.000</p>
                </div>
                
                <div class="rf-lottery-card">
                    <h3>Lotería Federal</h3>
                    <div class="rf-numbers">12345</div>
                    <p class="rf-prize">Premio: R$ 500.000</p>
                </div>
            </div>
            
            <div class="rf-cta">
                <a href="/verificar-boleto/" class="rf-button">¿Ganaste? Verifica tu boleto aquí →</a>
            </div>
        </div>';
    }
    
    public function shortcode_verificar_boleto($atts) {
        return '
        <div class="rf-verificar-boleto">
            <h1>Verifica tu número de boleto</h1>
            <p class="rf-intro">¿Tienes un boleto de lotería y quieres saber si es ganador? Usa nuestro verificador en línea para comprobarlo en segundos.</p>
            
            <form class="rf-verification-form" id="rf-ticket-form">
                <div class="rf-form-group">
                    <label for="lottery-type">Selecciona el tipo de lotería:</label>
                    <select id="lottery-type" name="lottery_type" required>
                        <option value="">Seleccionar...</option>
                        <option value="mega-sena">Mega-Sena</option>
                        <option value="lotofacil">Lotofácil</option>
                        <option value="quina">Quina</option>
                        <option value="federal">Lotería Federal</option>
                    </select>
                </div>
                
                <div class="rf-form-group">
                    <label for="ticket-numbers">Ingresa tu número de boleto:</label>
                    <input type="text" id="ticket-numbers" name="ticket_numbers" placeholder="Ej: 01-15-23-31-42-50" required>
                </div>
                
                <button type="submit" class="rf-button">Verificar</button>
            </form>
            
            <div id="rf-verification-result" class="rf-result" style="display:none;"></div>
        </div>';
    }
    
    public function shortcode_mega_sena($atts) {
        return '
        <div class="rf-mega-sena">
            <h1>Resultados de la Mega-Sena</h1>
            <p class="rf-intro">Aquí tienes los resultados más recientes de la Mega-Sena, la lotería más popular de Brasil. Consulta los sorteos anteriores, los premios acumulados y mucho más.</p>
            
            <div class="rf-latest-result">
                <h2>Último Sorteo</h2>
                <div class="rf-mega-numbers">' . $this->generate_sample_numbers(6, 'mega') . '</div>
                <p class="rf-draw-info">Sorteo #2547 - ' . date('d/m/Y') . '</p>
                <p class="rf-prize">Premio acumulado: R$ 45.000.000</p>
            </div>
            
            <div class="rf-statistics">
                <h3>Estadísticas</h3>
                <div class="rf-stats-grid">
                    <div class="rf-stat-item">
                        <span class="rf-stat-number">16</span>
                        <span class="rf-stat-label">Número más sorteado</span>
                    </div>
                    <div class="rf-stat-item">
                        <span class="rf-stat-number">13</span>
                        <span class="rf-stat-label">Número menos sorteado</span>
                    </div>
                    <div class="rf-stat-item">
                        <span class="rf-stat-number">1 en 50M</span>
                        <span class="rf-stat-label">Probabilidad de ganar</span>
                    </div>
                </div>
            </div>
            
            <div class="rf-tip">
                <h3>💡 Tip útil</h3>
                <p>¿Sabías que jugar combinaciones impares y pares aumenta tus posibilidades?</p>
            </div>
        </div>';
    }
    
    public function shortcode_calendario_sorteos($atts) {
        return '
        <div class="rf-calendario-sorteos">
            <h1>Calendario Oficial de Loterías</h1>
            <p class="rf-intro">Consulta cuándo se realizarán los próximos sorteos de tu lotería favorita. Esta página se actualiza automáticamente.</p>
            
            <div class="rf-calendar-grid">
                <div class="rf-calendar-item">
                    <h3>Mega-Sena</h3>
                    <div class="rf-next-draw">
                        <span class="rf-date">' . date('d/m/Y', strtotime('+3 days')) . '</span>
                        <span class="rf-time">20:00</span>
                    </div>
                    <p>Miércoles y Sábado</p>
                </div>
                
                <div class="rf-calendar-item">
                    <h3>Quina</h3>
                    <div class="rf-next-draw">
                        <span class="rf-date">' . date('d/m/Y', strtotime('+1 day')) . '</span>
                        <span class="rf-time">20:00</span>
                    </div>
                    <p>Lunes a Sábado</p>
                </div>
                
                <div class="rf-calendar-item">
                    <h3>Lotofácil</h3>
                    <div class="rf-next-draw">
                        <span class="rf-date">' . date('d/m/Y', strtotime('+1 day')) . '</span>
                        <span class="rf-time">20:00</span>
                    </div>
                    <p>Lunes a Viernes</p>
                </div>
            </div>
            
            <div class="rf-cta">
                <button class="rf-button" onclick="alert(\'Función de calendario próximamente\')">Agregar a mi calendario 📅</button>
            </div>
        </div>';
    }
    
    public function shortcode_como_jugar_lotofacil($atts) {
        return '
        <div class="rf-como-jugar">
            <h1>Aprende a Jugar Lotofácil</h1>
            <p class="rf-intro">Lotofácil es una de las loterías más sencillas de Brasil. Aprende cómo jugar, qué combinaciones elegir y cómo mejorar tus posibilidades de ganar.</p>
            
            <div class="rf-guide-sections">
                <div class="rf-guide-section">
                    <h2>¿Qué es Lotofácil?</h2>
                    <p>Lotofácil es un juego de lotería donde debes elegir entre 15 y 18 números del 1 al 25. Ganas si aciertas 11, 12, 13, 14 o 15 números sorteados.</p>
                </div>
                
                <div class="rf-guide-section">
                    <h2>¿Cuánto cuesta jugar?</h2>
                    <ul>
                        <li>15 números: R$ 2,50</li>
                        <li>16 números: R$ 40,00</li>
                        <li>17 números: R$ 340,00</li>
                        <li>18 números: R$ 2.040,00</li>
                    </ul>
                </div>
                
                <div class="rf-guide-section">
                    <h2>Reglas básicas</h2>
                    <ol>
                        <li>Elige de 15 a 18 números del 1 al 25</li>
                        <li>Los sorteos ocurren de lunes a viernes a las 20:00</li>
                        <li>Puedes apostar hasta las 19:00 del día del sorteo</li>
                        <li>Los premios se pagan inmediatamente</li>
                    </ol>
                </div>
                
                <div class="rf-guide-section">
                    <h2>Consejos de expertos</h2>
                    <ul>
                        <li>Combina números pares e impares</li>
                        <li>No elijas solo números consecutivos</li>
                        <li>Usa la estrategia de números fríos y calientes</li>
                        <li>Considera jugar en grupo para reducir costos</li>
                    </ul>
                </div>
            </div>
            
            <div class="rf-cta">
                <button class="rf-button" onclick="generateRandomNumbers()">Generador de Números Aleatorios</button>
                <div id="random-numbers" style="display:none; margin-top:20px;">
                    <h3>Números sugeridos:</h3>
                    <div class="rf-numbers" id="generated-numbers"></div>
                </div>
            </div>
        </div>';
    }
    
    public function shortcode_como_cobrar_premio($atts) {
        return '
        <div class="rf-como-cobrar">
            <h1>Guía para Cobrar tu Premio de Lotería</h1>
            <p class="rf-intro">¿Ganaste un premio? ¡Felicidades! Te explicamos cómo puedes reclamar tu dinero de forma segura y legal.</p>
            
            <div class="rf-claim-guide">
                <div class="rf-claim-section">
                    <h2>¿Dónde cobrar?</h2>
                    <div class="rf-claim-options">
                        <div class="rf-claim-option">
                            <h3>Premios hasta R$ 1.332,78</h3>
                            <p>Agencias de lotería, bancos Caixa</p>
                        </div>
                        <div class="rf-claim-option">
                            <h3>Premios de R$ 1.332,79 a R$ 2.112,00</h3>
                            <p>Solo bancos Caixa</p>
                        </div>
                        <div class="rf-claim-option">
                            <h3>Premios superiores a R$ 2.112,00</h3>
                            <p>Solo en Caixa Econômica Federal</p>
                        </div>
                    </div>
                </div>
                
                <div class="rf-claim-section">
                    <h2>Documentos necesarios</h2>
                    <ul>
                        <li>Boleto premiado original</li>
                        <li>Documento de identidad con foto</li>
                        <li>CPF</li>
                        <li>Comprobante de residencia</li>
                    </ul>
                </div>
                
                <div class="rf-claim-section">
                    <h2>Plazos para reclamar</h2>
                    <p><strong>90 días corridos</strong> a partir del sorteo. Después de este plazo, el premio prescribe.</p>
                </div>
                
                <div class="rf-claim-section">
                    <h2>Impuestos aplicables</h2>
                    <p>Premios superiores a R$ 1.903,98 están sujetos al Impuesto de Renta de 30%.</p>
                </div>
            </div>
            
            <div class="rf-tip">
                <h3>💡 Tip importante</h3>
                <p>Siempre guarda una copia de tu boleto y tómale una foto antes de cobrarlo.</p>
            </div>
        </div>';
    }
    
    public function shortcode_noticias_loteria($atts) {
        $news = $this->scrape_lottery_news();
        
        return '
        <div class="rf-noticias">
            <h1>Últimas Noticias del Mundo de las Loterías</h1>
            <p class="rf-intro">Mantente informado con las últimas noticias sobre loterías en Brasil y Latinoamérica. Entrevistas, ganadores, cambios en reglas y mucho más.</p>
            
            <div class="rf-news-grid">
                <article class="rf-news-item">
                    <h3>Ganador de R$50 millones revela estrategia</h3>
                    <p class="rf-news-date">' . date('d/m/Y') . '</p>
                    <p>Un afortunado ganador de São Paulo compartió los números que lo llevaron a ganar el mayor premio de la historia de Mega-Sena...</p>
                    <a href="' . $this->redirect_url . '" class="rf-read-more">Leer más →</a>
                </article>
                
                <article class="rf-news-item">
                    <h3>Cambios en las reglas de Quina desde julio 2025</h3>
                    <p class="rf-news-date">' . date('d/m/Y', strtotime('-1 day')) . '</p>
                    <p>Caixa Econômica Federal anunció modificaciones importantes en el sorteo de Quina que entrarán en vigor el próximo mes...</p>
                    <a href="' . $this->redirect_url . '" class="rf-read-more">Leer más →</a>
                </article>
                
                <article class="rf-news-item">
                    <h3>Nuevas loterías digitales ganan popularidad</h3>
                    <p class="rf-news-date">' . date('d/m/Y', strtotime('-2 days')) . '</p>
                    <p>El mercado de loterías online experimenta un crecimiento del 150% en comparación con el año anterior...</p>
                    <a href="' . $this->redirect_url . '" class="rf-read-more">Leer más →</a>
                </article>
            </div>
            
            <div class="rf-cta">
                <a href="' . $this->redirect_url . '" class="rf-button">Suscríbete a las noticias →</a>
            </div>
        </div>';
    }
    
    public function shortcode_resultados_vivo($atts) {
        return '
        <div class="rf-resultados-vivo">
            <h1>Sigue los Resultados en Vivo</h1>
            <p class="rf-intro">Observa los sorteos minuto a minuto desde nuestra plataforma. Los resultados se actualizan en tiempo real mientras ocurren los sorteos.</p>
            
            <div class="rf-live-status">
                <div class="rf-status-indicator">
                    <span class="rf-live-dot"></span>
                    <span>Próximo sorteo: Mega-Sena en 2 horas</span>
                </div>
            </div>
            
            <div class="rf-live-content">
                <div class="rf-live-draw">
                    <h2>Sorteo en Vivo - Mega-Sena</h2>
                    <div class="rf-live-numbers">
                        <div class="rf-number-ball">?</div>
                        <div class="rf-number-ball">?</div>
                        <div class="rf-number-ball">?</div>
                        <div class="rf-number-ball">?</div>
                        <div class="rf-number-ball">?</div>
                        <div class="rf-number-ball">?</div>
                    </div>
                    <p class="rf-live-time">Sorteo iniciará a las 20:00</p>
                </div>
                
                <div class="rf-live-features">
                    <h3>Características</h3>
                    <ul>
                        <li>✅ Sorteo en vivo de Mega-Sena y Quina</li>
                        <li>🔔 Alerta sonora cuando se actualiza un número</li>
                        <li>💬 Chat de la comunidad</li>
                        <li>📱 Notificaciones push</li>
                    </ul>
                </div>
            </div>
            
            <div class="rf-alert">
                <h3>⚠️ Aviso</h3>
                <p>Esta función usa datos en tiempo real. Asegúrate de tener una buena conexión de internet.</p>
            </div>
        </div>';
    }
    
    // Helper functions
    private function generate_sample_numbers($count, $type = 'default') {
        $numbers = array();
        $max = ($type === 'mega') ? 60 : 25;
        
        while (count($numbers) < $count) {
            $num = rand(1, $max);
            if (!in_array($num, $numbers)) {
                $numbers[] = $num;
            }
        }
        
        sort($numbers);
        
        $html = '';
        foreach ($numbers as $num) {
            $html .= '<span class="rf-number">' . sprintf('%02d', $num) . '</span>';
        }
        
        return $html;
    }
    
    private function scrape_lottery_results() {
        // This would contain actual scraping logic
        // For now, returning sample data
        return array();
    }
    
    private function scrape_lottery_news() {
        // This would contain actual news scraping logic
        // For now, returning sample data
        return array();
    }
    
    public function create_all_pages() {
        $pages = array(
            array(
                'title' => 'Resultados de Hoy',
                'slug' => 'resultados-de-hoy',
                'content' => '[rf_resultados_hoy]'
            ),
            array(
                'title' => 'Verifica Tu Boleto',
                'slug' => 'verificar-boleto',
                'content' => '[rf_verificar_boleto]'
            ),
            array(
                'title' => 'Mega-Sena',
                'slug' => 'mega-sena',
                'content' => '[rf_mega_sena]'
            ),
            array(
                'title' => 'Calendario de Sorteos',
                'slug' => 'calendario-sorteos',
                'content' => '[rf_calendario_sorteos]'
            ),
            array(
                'title' => 'Cómo Jugar Lotofácil',
                'slug' => 'como-jugar-lotofacil',
                'content' => '[rf_como_jugar_lotofacil]'
            ),
            array(
                'title' => 'Cómo Cobrar un Premio',
                'slug' => 'como-cobrar-premio',
                'content' => '[rf_como_cobrar_premio]'
            ),
            array(
                'title' => 'Noticias de Loterías',
                'slug' => 'noticias-loteria',
                'content' => '[rf_noticias_loteria]'
            ),
            array(
                'title' => 'Resultados en Vivo',
                'slug' => 'resultados-en-vivo',
                'content' => '[rf_resultados_vivo]'
            )
        );
        
        foreach ($pages as $page_data) {
            $existing_page = get_page_by_path($page_data['slug']);
            
            if (!$existing_page) {
                $page = array(
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $page_data['slug']
                );
                
                wp_insert_post($page);
            }
        }
    }
    
    public function activate() {
        // Clear any existing cache
        wp_cache_flush();
        
        // Create default pages
        $this->create_all_pages();
    }
    
    public function deactivate() {
        // Clear cache
        wp_cache_flush();
    }
}

// Initialize the plugin
new ResultadoFacilPlugin();

// AJAX handlers
add_action('wp_ajax_rf_verify_ticket', 'rf_verify_ticket_handler');
add_action('wp_ajax_nopriv_rf_verify_ticket', 'rf_verify_ticket_handler');

function rf_verify_ticket_handler() {
    check_ajax_referer('rf_ajax_nonce', 'nonce');
    
    $lottery_type = sanitize_text_field($_POST['lottery_type']);
    $ticket_numbers = sanitize_text_field($_POST['ticket_numbers']);
    
    // Simple random result for demonstration
    $is_winner = (rand(1, 100) <= 5); // 5% chance of winning
    
    if ($is_winner) {
        $message = '🎉 ¡Felicidades! Tu boleto es ganador. Premio estimado: R$ ' . number_format(rand(1000, 50000), 2, ',', '.');
        $class = 'winner';
    } else {
        $message = '😔 Lo sentimos, tu boleto no es ganador esta vez. ¡Suerte para la próxima!';
        $class = 'no-winner';
    }
    
    wp_send_json_success(array(
        'message' => $message,
        'class' => $class
    ));
}

// Add CSS inline (you can move this to a separate file later)
add_action('wp_head', 'rf_add_inline_styles');
function rf_add_inline_styles() {
    ?>
    <style>
    /* ResultadoFacil Plugin Styles */
    .rf-resultados-hoy, .rf-verificar-boleto, .rf-mega-sena, .rf-calendario-sorteos,
    .rf-como-jugar, .rf-como-cobrar, .rf-noticias, .rf-resultados-vivo {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Arial', sans-serif;
    }
    
    .rf-intro {
        font-size: 18px;
        color: #666;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .rf-lottery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }
    
    .rf-lottery-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        text-align: center;
        transition: transform 0.3s ease;
    }
    
    .rf-lottery-card:hover {
        transform: translateY(-5px);
    }
    
    .rf-lottery-card h3 {
        margin: 0 0 15px 0;
        font-size: 24px;
        font-weight: bold;
    }
    
    .rf-numbers {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
        margin: 15px 0;
    }
    
    .rf-number {
        background: rgba(255,255,255,0.9);
        color: #333;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }
    
    .rf-mega-numbers .rf-number {
        background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        color: white;
        width: 55px;
        height: 55px;
        font-size: 18px;
    }
    
    .rf-prize {
        font-size: 18px;
        font-weight: bold;
        margin: 15px 0 0 0;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    
    .rf-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .rf-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        color: white;
        text-decoration: none;
    }
    
    .rf-cta {
        text-align: center;
        margin: 40px 0;
    }
    
    /* Verification Form */
    .rf-verification-form {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 15px;
        max-width: 500px;
        margin: 30px auto;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .rf-form-group {
        margin-bottom: 20px;
    }
    
    .rf-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }
    
    .rf-form-group select,
    .rf-form-group input {
        width: 100%;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }
    
    .rf-form-group select:focus,
    .rf-form-group input:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .rf-result {
        margin-top: 20px;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        font-weight: bold;
        font-size: 18px;
    }
    
    .rf-result.winner {
        background: #d4edda;
        color: #155724;
        border: 2px solid #c3e6cb;
    }
    
    .rf-result.no-winner {
        background: #f8d7da;
        color: #721c24;
        border: 2px solid #f5c6cb;
    }
    
    /* Calendar */
    .rf-calendar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }
    
    .rf-calendar-item {
        background: white;
        border: 2px solid #667eea;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .rf-calendar-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
    }
    
    .rf-next-draw {
        margin: 15px 0;
    }
    
    .rf-date {
        display: block;
        font-size: 24px;
        font-weight: bold;
        color: #667eea;
    }
    
    .rf-time {
        display: block;
        font-size: 18px;
        color: #764ba2;
        margin-top: 5px;
    }
    
    /* Statistics */
    .rf-statistics {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 15px;
        margin: 30px 0;
    }
    
    .rf-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .rf-stat-item {
        text-align: center;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .rf-stat-number {
        display: block;
        font-size: 32px;
        font-weight: bold;
        color: #667eea;
    }
    
    .rf-stat-label {
        display: block;
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }
    
    /* Guide sections */
    .rf-guide-sections {
        margin: 30px 0;
    }
    
    .rf-guide-section {
        background: white;
        border-left: 4px solid #667eea;
        padding: 25px;
        margin-bottom: 20px;
        border-radius: 0 10px 10px 0;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    }
    
    .rf-guide-section h2 {
        color: #667eea;
        margin-top: 0;
    }
    
    .rf-guide-section ul,
    .rf-guide-section ol {
        padding-left: 20px;
    }
    
    .rf-guide-section li {
        margin-bottom: 8px;
        line-height: 1.6;
    }
    
    /* Claim guide */
    .rf-claim-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }
    
    .rf-claim-option {
        background: linear-gradient(135deg, #74b9ff, #0984e3);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    
    .rf-claim-option h3 {
        margin-top: 0;
        font-size: 18px;
    }
    
    /* News */
    .rf-news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin: 30px 0;
    }
    
    .rf-news-item {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .rf-news-item:hover {
        transform: translateY(-3px);
    }
    
    .rf-news-item h3 {
        color: #333;
        margin-top: 0;
        font-size: 20px;
    }
    
    .rf-news-date {
        color: #667eea;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .rf-read-more {
        color: #667eea;
        text-decoration: none;
        font-weight: bold;
    }
    
    .rf-read-more:hover {
        text-decoration: underline;
    }
    
    /* Live results */
    .rf-live-status {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
        text-align: center;
    }
    
    .rf-status-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: bold;
    }
    
    .rf-live-dot {
        width: 12px;
        height: 12px;
        background: #e74c3c;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .rf-live-draw {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        margin: 20px 0;
    }
    
    .rf-live-numbers {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 20px 0;
    }
    
    .rf-number-ball {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.9);
        color: #333;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }
    
    .rf-live-features {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 10px;
        margin: 20px 0;
    }
    
    .rf-live-features ul {
        list-style: none;
        padding: 0;
    }
    
    .rf-live-features li {
        margin-bottom: 10px;
        font-size: 16px;
    }
    
    /* Tips and alerts */
    .rf-tip, .rf-alert {
        padding: 20px;
        border-radius: 10px;
        margin: 30px 0;
    }
    
    .rf-tip {
        background: #e8f5e8;
        border-left: 4px solid #28a745;
    }
    
    .rf-alert {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
    }
    
    .rf-tip h3, .rf-alert h3 {
        margin-top: 0;
        color: #333;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .rf-lottery-grid,
        .rf-calendar-grid,
        .rf-news-grid {
            grid-template-columns: 1fr;
        }
        
        .rf-numbers {
            justify-content: center;
        }
        
        .rf-number {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }
        
        .rf-mega-numbers .rf-number {
            width: 45px;
            height: 45px;
            font-size: 16px;
        }
        
        .rf-live-numbers {
            flex-wrap: wrap;
        }
        
        .rf-number-ball {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
    }
    </style>
    
    <script>
    // JavaScript functions for the plugin
    function generateRandomNumbers() {
        const numbers = [];
        while (numbers.length < 15) {
            const num = Math.floor(Math.random() * 25) + 1;
            if (!numbers.includes(num)) {
                numbers.push(num);
            }
        }
        numbers.sort((a, b) => a - b);
        
        const container = document.getElementById('generated-numbers');
        container.innerHTML = numbers.map(num => 
            `<span class="rf-number">${num.toString().padStart(2, '0')}</span>`
        ).join('');
        
        document.getElementById('random-numbers').style.display = 'block';
    }
    
    // Ticket verification form handler
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('rf-ticket-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                formData.append('action', 'rf_verify_ticket');
                formData.append('nonce', rf_ajax.nonce);
                
                const resultDiv = document.getElementById('rf-verification-result');
                resultDiv.innerHTML = '<p>Verificando...</p>';
                resultDiv.style.display = 'block';
                
                fetch(rf_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `<p>${data.data.message}</p>`;
                        resultDiv.className = `rf-result ${data.data.class}`;
                    } else {
                        resultDiv.innerHTML = '<p>Error al verificar el boleto. Inténtalo de nuevo.</p>';
                        resultDiv.className = 'rf-result no-winner';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<p>Error de conexión. Inténtalo de nuevo.</p>';
                    resultDiv.className = 'rf-result no-winner';
                });
            });
        }
        
        // Auto-refresh live results every 30 seconds
        if (document.querySelector('.rf-resultados-vivo')) {
            setInterval(function() {
                // This would refresh live data in a real implementation
                console.log('Refreshing live results...');
            }, 30000);
        }
    });
    </script>
    <?php
}

// Additional AJAX handlers for enhanced functionality
add_action('wp_ajax_rf_get_latest_results', 'rf_get_latest_results_handler');
add_action('wp_ajax_nopriv_rf_get_latest_results', 'rf_get_latest_results_handler');

function rf_get_latest_results_handler() {
    check_ajax_referer('rf_ajax_nonce', 'nonce');
    
    // In a real implementation, this would fetch actual lottery results
    $results = array(
        'mega_sena' => array(
            'numbers' => array(12, 23, 34, 45, 56, 60),
            'prize' => 'R$ 45.000.000',
            'date' => date('d/m/Y')
        ),
        'lotofacil' => array(
            'numbers' => array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 2, 4),
            'prize' => 'R$ 1.500.000',
            'date' => date('d/m/Y')
        )
    );
    
    wp_send_json_success($results);
}

// Cron job for updating results automatically
add_action('wp', 'rf_schedule_results_update');
function rf_schedule_results_update() {
    if (!wp_next_scheduled('rf_update_results_cron')) {
        wp_schedule_event(time(), 'hourly', 'rf_update_results_cron');
    }
}

add_action('rf_update_results_cron', 'rf_update_results_automatically');
function rf_update_results_automatically() {
    // This would contain the logic to scrape and update results
    // Clear cache after update
    wp_cache_flush();
}

// Clean up scheduled events on deactivation
register_deactivation_hook(__FILE__, 'rf_deactivation_cleanup');
function rf_deactivation_cleanup() {
    wp_clear_scheduled_hook('rf_update_results_cron');
}

?>