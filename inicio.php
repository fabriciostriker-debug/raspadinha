<?php
// Verificar e incluir sistema de rastreamento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!file_exists(__DIR__ . '/sess.php')) {
    die('Arquivo de sistema necessário não encontrado.');
}

// Incluir o sistema de rastreamento
define('SESS_INCLUDED', true);
require_once 'sess.php';

// Verificar se o rastreamento foi executado
if (!defined('SESS_EXECUTED')) {
    die('Erro no sistema de rastreamento.');
}

// Incluir o header
require_once 'includes/header.php';
?>


    <!-- Main Content -->
    <div class="flex-grow">
        <!-- Banner Carousel Section -->
        <section class="py-8 px-4">
            <div class="max-w-6xl mx-auto">
                <div class="carousel-container">
                    <!-- Slide 1 -->
                    <?php
                    // Buscar banners do carousel da nova tabela
                    $carousel_banners_query = $conn->query("SELECT * FROM carousel_banners ORDER BY position ASC LIMIT 2");
                    $carousel_banners = [];
                    while ($banner = $carousel_banners_query->fetch_assoc()) {
                        $carousel_banners[] = $banner;
                    }
                    
                    // Se houver banners cadastrados, mostrar
                    if (count($carousel_banners) > 0): 
                        foreach ($carousel_banners as $index => $banner): 
                    ?>
                    <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo $banner['image_url']; ?>')">
                        <div class="">
                            <div class="carousel-content">
                            </div>
                        </div>
                    </div>
                    <?php 
                        endforeach;
                    // Se não houver banners, mostrar os padrões
                    else: 
                    ?>
                    <div class="carousel-slide active" style="background-image: url('img/NOVOS-BANNER-RASPA.webp')">
                        <div class="">
                            <div class="carousel-content">
                            </div>
                        </div>
                    </div>
                    <div class="carousel-slide" style="background-image: url('img/NOVOS-BANNER-RASPA2 (1).webp')">
                        <div class="">
                            <div class="carousel-content">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- Indicators -->
                    <div class="carousel-indicators">
                        <div class="indicator active" onclick="goToSlide(0)"></div>
                        <div class="indicator" onclick="goToSlide(1)"></div>
                    </div>
                </div>

                <!-- Live Winners Carousel - LAYOUT HORIZONTAL SEMPRE -->
                <div class="mt-8 p-4">
                    <div class="live-winners-section">
                        <!-- Título "AO VIVO" sempre horizontal -->
                        <div class="live-winners-title">
                            <svg viewBox="0 0 59 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10 flex-shrink-0">
                                <path d="M2.381 31.8854L0.250732 32.1093L5.76436 16.3468L8.04082 16.1075L13.5753 30.7088L11.4242 30.9349L10.0667 27.2976L3.71764 27.9649L2.381 31.8854ZM6.64153 19.5306L4.34418 26.114L9.461 25.5762L7.14277 19.4779C7.101 19.3283 7.05227 19.1794 6.99657 19.0313C6.94088 18.8691 6.90607 18.7328 6.89215 18.6222C6.8643 18.7372 6.82949 18.8808 6.78772 19.0532C6.74595 19.2116 6.69722 19.3707 6.64153 19.5306Z" fill="#7B869D"></path>
                                <path d="M28.5469 21.5332C28.5469 23.0732 28.2336 24.4711 27.6071 25.727C26.9945 26.9674 26.1382 27.9814 25.0382 28.769C23.9522 29.5411 22.6922 30.0026 21.2581 30.1533C19.8518 30.3011 18.5987 30.1038 17.4988 29.5614C16.4128 29.0036 15.5634 28.1688 14.9508 27.0572C14.3382 25.9456 14.0319 24.6128 14.0319 23.0588C14.0319 21.5188 14.3382 20.1286 14.9508 18.8882C15.5774 17.6464 16.4336 16.6324 17.5197 15.8462C18.6057 15.0601 19.8588 14.5924 21.2789 14.4431C22.7131 14.2924 23.9731 14.4959 25.0591 15.0538C26.1451 15.6117 26.9945 16.4464 27.6071 17.558C28.2336 18.6681 28.5469 19.9932 28.5469 21.5332ZM26.3958 21.7593C26.3958 20.5833 26.18 19.577 25.7483 18.7404C25.3306 17.9023 24.7389 17.2855 23.9731 16.8899C23.2073 16.4804 22.3093 16.3298 21.2789 16.4381C20.2625 16.5449 19.3715 16.8836 18.6057 17.4541C17.8399 18.0106 17.2412 18.7525 16.8096 19.6799C16.3919 20.6058 16.183 21.6567 16.183 22.8327C16.183 24.0087 16.3919 25.0158 16.8096 25.8539C17.2412 26.6905 17.8399 27.3136 18.6057 27.7231C19.3715 28.1326 20.2625 28.2839 21.2789 28.1771C22.3093 28.0688 23.2073 27.7294 23.9731 27.1589C24.7389 26.5745 25.3306 25.8193 25.7483 24.8934C26.18 23.966 26.3958 22.9213 26.3958 21.7593Z" fill="#7B869D"></path>
                                <path d="M5.74539 52.1851L0.200195 37.8724L3.66344 37.5084L6.46607 44.7421C6.63956 45.1801 6.79971 45.6397 6.94652 46.1208C7.09332 46.6018 7.2468 47.156 7.40695 47.7833C7.59379 47.0525 7.76061 46.4445 7.90742 45.9594C8.06757 45.4729 8.22772 44.9998 8.38787 44.5401L11.1505 36.7215L14.5336 36.3659L9.08853 51.8337L5.74539 52.1851Z" fill="#00E880"></path>
                                <path d="M19.3247 35.8623V50.7578L16.0816 51.0987V36.2032L19.3247 35.8623Z" fill="#00E880"></path>
                                <path d="M26.4195 50.0121L20.8743 35.6995L24.3375 35.3355L27.1401 42.5692C27.3136 43.0072 27.4738 43.4667 27.6206 43.9478C27.7674 44.4289 27.9209 44.9831 28.081 45.6104C28.2679 44.8795 28.4347 44.2716 28.5815 43.7864C28.7416 43.2999 28.9018 42.8268 29.0619 42.3672L31.8245 34.5486L35.2077 34.193L29.7626 49.6608L26.4195 50.0121Z" fill="#00E880"></path>
                                <path d="M49.647 40.1029C49.647 41.6193 49.3401 42.9935 48.7261 44.2255C48.1122 45.4441 47.2581 46.4397 46.1637 47.2123C45.0694 47.9714 43.8015 48.4268 42.3602 48.5782C40.9322 48.7283 39.671 48.5388 38.5766 48.0097C37.4956 47.4658 36.6482 46.6491 36.0343 45.5595C35.4337 44.4686 35.1334 43.1649 35.1334 41.6485C35.1334 40.1321 35.4404 38.7646 36.0543 37.5461C36.6682 36.314 37.5156 35.3192 38.5967 34.5614C39.691 33.7889 40.9522 33.3275 42.3802 33.1774C43.8216 33.0259 45.0827 33.2222 46.1637 33.7661C47.2581 34.2952 48.1122 35.1045 48.7261 36.1941C49.3401 37.2836 49.647 38.5866 49.647 40.1029ZM46.2238 40.4627C46.2238 39.51 46.0703 38.7142 45.7634 38.0755C45.4564 37.4234 45.016 36.9463 44.4421 36.6443C43.8816 36.3409 43.201 36.2313 42.4002 36.3155C41.5995 36.3996 40.9122 36.653 40.3383 37.0757C39.7644 37.4983 39.324 38.0679 39.017 38.7846C38.7101 39.4878 38.5566 40.3158 38.5566 41.2686C38.5566 42.2214 38.7101 43.0238 39.017 43.6759C39.324 44.3281 39.7644 44.8051 40.3383 45.1071C40.9122 45.4091 41.5995 45.5181 42.4002 45.4339C43.201 45.3497 43.8816 45.097 44.4421 44.6758C45.016 44.2398 45.4564 43.6634 45.7634 42.9467C46.0703 42.2301 46.2238 41.4021 46.2238 40.4627Z" fill="#00E880"></path>
                                <circle cx="39" cy="20" r="6" fill="#222733"></circle>
                                <g filter="url(#filter0_d_726_17235)">
                                    <circle cx="39" cy="20" r="3.75" fill="#00E880"></circle>
                                </g>
                                <defs>
                                    <filter id="filter0_d_726_17235" x="31.25" y="16.25" width="15.5" height="15.5" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                                        <feOffset dy="4"/>
                                        <feGaussianBlur stdDeviation="2"/>
                                        <feComposite in2="hardAlpha" operator="out"/>
                                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.909804 0 0 0 0 0.501961 0 0 0 0.25 0"/>
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_726_17235"/>
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_726_17235" result="shape"/>
                                    </filter>
                                </defs>
                            </svg>
                            <div class="text-white">
                            </div>
                        </div>
                                                
                        <!-- Carousel dos ganhadores -->
                        <div class="live-winners-carousel">
                            <div class="winners-carousel-container">
                                <div class="winners-carousel">
                                    <!-- Winner Card 1 -->
                                    <div class="winner-card">
                                        <img src="img/1K.webp" class="object-contain rounded" alt="1000 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">Maria S***</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">1000 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span>1.000,00</span>
                                        </div>
                                    </div>
                                    <!-- Winner Card 2 -->
                                    <div class="winner-card">
                                        <img src="img/2K.webp" class="object-contain rounded" alt="2000 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">João P****</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">2000 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span>2.000,00</span>
                                        </div>
                                    </div>
                                    <!-- Winner Card 3 -->
                                    <div class="winner-card">
                                        <img src="img/5 REAIS.webp" class="object-contain rounded" alt="5 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">Ana C*****</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">5 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span>5,00</span>
                                        </div>
                                    </div>
                                    <!-- Winner Card 4 -->
                                    <div class="winner-card">
                                        <img src="img/5.webp" class="object-contain rounded" alt="5000 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">Carlos M***</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">5000 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span>5.000,00</span>
                                        </div>
                                    </div>
                                    <!-- Winner Card 5 -->
                                    <div class="winner-card">
                                        <img src="img/10.webp" class="object-contain rounded" alt="10000 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">Fernanda L****</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">10000 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span>10.000,00</span>
                                        </div>
                                    </div>
                                    <!-- Winner Card 6 -->
                                    <div class="winner-card">
                                        <img src="img/50-CENTAVOS-2.webp" class="object-contain rounded" alt="50 Centavos">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">Roberto S****</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">50 Centavos</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span>0,50</span>
                                        </div>
                                    </div>
                                    <!-- Winner Card 7 -->
                                    <div class="winner-card">
                                        <img src="img/500-REAIS.webp" class="object-contain rounded" alt="500 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">Luciana M****</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">500 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span>500,00</span>
                                        </div>
                                    </div>
                                    <!-- Duplicate cards for seamless loop -->
                                    <div class="winner-card">
                                        <img src="img/1K.webp" class="object-contain rounded" alt="1000 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">Maria S***</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">1000 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span> 1.000,00</span>
                                        </div>
                                    </div>
                                    <div class="winner-card">
                                        <img src="img/2K.webp" class="object-contain rounded" alt="2000 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">João P****</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">2000 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span> 2.000,00</span>
                                        </div>
                                    </div>
                                    <div class="winner-card">
                                        <img src="img/5 REAIS.webp" class="object-contain rounded" alt="5 Reais">
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-amber-400/75 text-ellipsis overflow-hidden whitespace-nowrap">Ana C*****</span>
                                            <span class="font-medium text-gray-300 text-ellipsis overflow-hidden whitespace-nowrap text-xs">5 Reais</span>
                                            <span class="font-semibold text-xs"><span class="text-emerald-300">R$ </span> 5,00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO DE BADGES DE SEGURANÇA -->
                <div class="security-badges">
                    <div class="security-badge">
                        <i class="fas fa-shield-check"></i>
                        <span>100% Seguro</span>
                    </div>
                    <div class="security-badge ssl-badge">
                        <i class="fas fa-lock"></i>
                        <span>SSL Criptografado</span>
                    </div>
                    <div class="security-badge pix-badge">
                        <i class="fab fa-pix"></i>
                        <span>PIX Instantâneo</span>
                    </div>
                    <div class="security-badge">
                        <i class="fas fa-user-shield"></i>
                        <span>Dados Protegidos</span>
                    </div>
                </div>

                

                <!-- NOVA SEÇÃO: Título Destaques -->
                <div class="destaques-header">
                    <svg width="1em" height="1em" fill="currentColor" class="destaques-icon bi bi-fire text-amber-400" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"></path>
                    </svg>
                    <h2 class="destaques-title">Destaques</h2>
                </div>

                <!-- Raspadinhas Grid -->
                <div class="raspadinha-grid">
                    <!-- Raspadinha R$ 1,00 -->
                    <div class="raspadinha-card raspadinha-card-enhanced glow-effect">
                        <!-- Badge Novo -->
                        <div class="novo-badge">✨ NOVO</div>
                        <!-- Selo de verificação -->
                        <div class="verified-seal">✓</div>
                        <!-- Indicador de prêmio -->
                        <div class="premio-indicator">💰 Até R$ 1.000</div>
                        
                        <img src="https://i.ibb.co/dTqBHS1/PREMIOS-DIVERSOS-1.jpg" alt="Ícone" class="w-full h-16 object-cover mt-3 sm:mt-3 mb-4 rounded-md" />
                        <p class="prize-text">Prêmios até<br>R$ 1.000,00 NO PIX</p>
                        <span class="price-badge green">R$ 1,00</span>
                        <p class="game-description">Sonho de Consumo 😍<br>Prêmio até: R$ 1.000,00</p>
                        <?php if ($usuarioLogado): ?>
                            <a href="jogo?valor=1" class="play-button btn-premium">
                                <i class="fas fa-play mr-2"></i> JOGAR AGORA
                            </a>
                        <?php else: ?>
                            <button onclick="abrirModal('login')" class="play-button login-required">
                                <i class="fas fa-sign-in-alt mr-2"></i> ENTRAR PARA JOGAR
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Raspadinha R$ 5,00 -->
                    <div class="raspadinha-card raspadinha-card-enhanced sparkle-effect">
                        <!-- Badge HOT -->
                        <div class="hot-badge-card">🔥 HOT</div>
                        <!-- Selo de verificação -->
                        <div class="verified-seal">✓</div>
                        <!-- Indicador de prêmio -->
                        <div class="premio-indicator">🎯 Até R$ 5.000</div>
                        
                        <img src="https://i.ibb.co/twq9SSqZ/PADR-O-02.jpg" alt="Ícone" class="w-full h-16 object-cover mt-3 sm:mt-3 mb-4 rounded-md" />
                        <p class="prize-text">Prêmios até<br>R$ 5.000,00 NO PIX</p>
                        <span class="price-badge orange">R$ 5,00</span>
                        <p class="game-description">Raspe da Emoção<br>Prêmio até: R$ 5.000,00</p>
                        <?php if ($usuarioLogado): ?>
                            <a href="jogo?valor=5" class="play-button btn-premium">
                                <i class="fas fa-play mr-2"></i> JOGAR AGORA
                            </a>
                        <?php else: ?>
                            <button onclick="abrirModal('login')" class="play-button login-required">
                                <i class="fas fa-sign-in-alt mr-2"></i> ENTRAR PARA JOGAR
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Raspadinha R$ 10,00 -->
                    <div class="raspadinha-card raspadinha-card-enhanced particle-effect">
                        <!-- Badge Popular -->
                        <div class="popular-badge">👑 POPULAR</div>
                        <!-- Selo de verificação -->
                        <div class="verified-seal">✓</div>
                        <!-- Indicador de prêmio -->
                        <div class="premio-indicator">💎 Até R$ 6.300</div>
                        
                        <img src="https://i.ibb.co/V0GZT5V2/PADR-O-03.jpg" alt="Ícone" class="w-full h-16 object-cover mt-3 sm:mt-3 mb-4 rounded-md" />
                        <p class="prize-text">Prêmios até<br>R$ 6.300,00 NO PIX</p>
                        <span class="price-badge red">R$ 10,00</span>
                        <p class="game-description">Me mimei<br>Prêmio até: R$ 6.300,00</p>
                        <?php if ($usuarioLogado): ?>
                            <a href="jogo?valor=10" class="play-button btn-premium">
                                <i class="fas fa-play mr-2"></i> JOGAR AGORA
                            </a>
                        <?php else: ?>
                            <button onclick="abrirModal('login')" class="play-button login-required">
                                <i class="fas fa-sign-in-alt mr-2"></i> ENTRAR PARA JOGAR
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Raspadinha R$ 20,00 -->
                    <div class="raspadinha-card raspadinha-card-enhanced">
                        <!-- Badge Limitado -->
                        <div class="limitado-badge">⏰ LIMITADO</div>
                        <!-- Selo de verificação -->
                        <div class="verified-seal">✓</div>
                        <!-- Indicador de prêmio -->
                        <div class="premio-indicator">🚀 Até R$ 7.500</div>
                        
                        <img src="https://i.ibb.co/hRXjDdPh/BIKE-MAQUINA-MOTO.jpg" alt="Ícone" class="w-full h-16 object-cover mt-3 sm:mt-3 mb-4 rounded-md" />
                        <p class="prize-text">Prêmios até<br>R$ 7.500,00 NO PIX</p>
                        <span class="price-badge" style="background: linear-gradient(135deg, #8b5cf6, #a855f7); color: white;">R$ 20,00</span>
                        <p class="game-description">Super Prêmios<br>Prêmio até: R$ 7.000,00</p>
                        <?php if ($usuarioLogado): ?>
                            <a href="jogo?valor=20" class="play-button btn-premium">
                                <i class="fas fa-play mr-2"></i> JOGAR AGORA
                            </a>
                        <?php else: ?>
                            <button onclick="abrirModal('login')" class="play-button login-required">
                                <i class="fas fa-sign-in-alt mr-2"></i> ENTRAR PARA JOGAR
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Raspadinha R$ 50,00 -->
                    <div class="raspadinha-card raspadinha-card-enhanced glow-effect sparkle-effect">
                        <!-- Badge HOT -->
                        <div class="hot-badge-card">🔥 MUITO HOT</div>
                        <!-- Selo de verificação -->
                        <div class="verified-seal">✓</div>
                        <!-- Indicador de prêmio -->
                        <div class="premio-indicator">💰 Até R$ 11.000</div>
                        
                        <img src="https://i.ibb.co/HLDpqfR0/CONSOLES.jpg" alt="Ícone" class="w-full h-16 object-cover mt-3 sm:mt-3 mb-4 rounded-md" />
                        <p class="prize-text">Prêmios até<br>R$ 11.000,00 NO PIX</p>
                        <span class="price-badge green">R$ 50,00</span>
                        <p class="game-description">Sonho de Consumo 😍<br>Prêmio até: R$ 11.000,00</p>
                        <?php if ($usuarioLogado): ?>
                            <a href="jogo?valor=50" class="play-button btn-premium">
                                <i class="fas fa-play mr-2"></i> JOGAR AGORA
                            </a>
                        <?php else: ?>
                            <button onclick="abrirModal('login')" class="play-button login-required">
                                <i class="fas fa-sign-in-alt mr-2"></i> ENTRAR PARA JOGAR
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Raspadinha R$ 100,00 -->
                    <div class="raspadinha-card raspadinha-card-enhanced animated-gradient particle-effect">
                        <!-- Badge HOT -->
                        <div class="hot-badge-card">💎 PREMIUM VIP</div>
                        <!-- Selo de verificação -->
                        <div class="verified-seal">✓</div>
                        <!-- Indicador de prêmio -->
                        <div class="premio-indicator">👑 Até R$ 14.000</div>
                        
                        <img src="https://i.ibb.co/d05zpNfL/luxo-1.jpg" alt="Ícone" class="w-full h-16 object-cover mt-3 sm:mt-3 mb-4 rounded-md" />
                        <p class="prize-text">Prêmios até<br>R$ 14.000,00 NO PIX</p>
                        <span class="price-badge green">R$ 100,00</span>
                        <p class="game-description">Sonho de Consumo 😍<br>Prêmio até: R$ 14.000,00</p>
                        <?php if ($usuarioLogado): ?>
                            <a href="jogo?valor=100" class="play-button btn-premium">
                                <i class="fas fa-play mr-2"></i> JOGAR AGORA
                            </a>
                        <?php else: ?>
                            <button onclick="abrirModal('login')" class="play-button login-required">
                                <i class="fas fa-sign-in-alt mr-2"></i> ENTRAR PARA JOGAR
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <?php include __DIR__ . '/includes/footer.php'; ?>

    </div>

    <?php include __DIR__ . '/includes/mobile_nav.php'; ?>

    <!-- Modais Compactos -->
            <div id="modalContainer" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[1001] hidden">
        <div class="modal-compact bg-black text-white rounded-2xl shadow-2xl w-[90%] relative transform transition-all border border-green-500">
<button onclick="fecharModal()" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 font-bold text-xl transition-colors z-20">
    <i class="fas fa-times"></i>
</button>


            <!-- LOGIN PREMIUM - NOVO DESIGN IGUAL AO REGISTRO -->
<div id="modalLogin" class="hidden">
    <!-- HOT Badge -->
    <div class="absolute top-0 right-0 z-10">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg animate-pulse">
            ⚡ RÁPIDO
        </div>
    </div>
    
    <!-- Header Premium -->
    <div class="modal-header text-center relative overflow-hidden">
        <!-- Background Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-t-2xl"></div>
        
        <!-- Content -->
        <div class="relative z-10">
            <!-- Icon with Glow Effect -->
            <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-2xl relative">
                <!-- Efeito de pulsação -->
                <div class="absolute inset-0 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full animate-ping opacity-30"></div>
                <!-- Ícone de Login -->
                <svg class="w-6 h-6 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            
            <h2 class="text-lg font-black text-white mb-1">🚀 Entrar na Conta</h2>
            <p class="text-green-300 font-semibold text-xs">Acesse sua conta e continue ganhando!</p>
            
            <!-- Security Badges -->
            <div class="flex justify-center gap-2 mt-3 flex-wrap">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100/20 text-green-300 border border-green-400/300">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Login Seguro
                </span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100/20 text-green-300 border border-green-400/30">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Acesso Rápido
                </span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100/20 text-purple-300 border border-purple-400/30">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Criptografado
                </span>
            </div>
        </div>
    </div>

    <!-- Form Premium -->
    <form id="formLogin" class="space-y-3 mt-4">
        <!-- Email -->
        <div class="relative">
            <label class="block text-xs font-bold mb-1 text-green-400 flex items-center">
                <i class="fas fa-envelope mr-1"></i> Email
            </label>
            <input type="email" name="email" placeholder="seu@email.com" required 
                   class="form-input w-full rounded-xl bg-gray-800/50 border-2 border-gray-600/50 text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition-all duration-300 pl-4 pr-4 py-3 backdrop-blur-sm">
        </div>

        <!-- Senha -->
        <div class="relative">
            <label class="block text-xs font-bold mb-1 text-green-400 flex items-center">
                <i class="fas fa-lock mr-1"></i> Senha
            </label>
            <div class="relative">
                <input type="password" id="senhaLogin" name="senha" placeholder="Sua senha" required 
                       class="form-input w-full rounded-xl bg-gray-800/50 border-2 border-gray-600/50 text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition-all duration-300 pl-4 pr-12 py-3 backdrop-blur-sm">
                <button type="button" onclick="togglePassword('senhaLogin', 'toggleSenhaLogin')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i id="toggleSenhaLogin" class="fas fa-eye text-gray-400 hover:text-green-400 transition-colors"></i>
                </button>
            </div>
        </div>

        <!-- Benefits Banner -->
        <div class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 border border-green-500/30 rounded-xl p-3 my-4">
            <div class="text-center">
                <div class="text-green-400 font-bold text-sm mb-2">⚡ ACESSO INSTANTÂNEO:</div>
                <div class="flex justify-center gap-4 text-xs text-green-300">
                    <div class="flex items-center">
                        <i class="fas fa-gamepad mr-1"></i>
                        <span>Continue Jogando</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-coins mr-1"></i>
                        <span>Seus Ganhos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão Premium -->
        <button type="submit" class="form-button w-full bg-gradient-to-r from-green-500 via-emerald-500 to-green-600 hover:from-green-600 hover:via-emerald-600 hover:to-green-700 text-white font-black py-4 rounded-xl transition-all transform hover:scale-105 active:scale-95 shadow-2xl relative overflow-hidden text-base">
            <!-- Efeito de brilho -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-full group-hover:translate-x-full transition-transform duration-1000"></div>
            <!-- Conteúdo -->
            <div class="relative z-10 flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt text-lg"></i>
                <span>ENTRAR E JOGAR AGORA</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </button>

        <!-- Error Messages -->
        <p id="loginErro" ctext-green-400 text-xs text-center hidden bg-red-500/10 border border-red-500/30 rounded-xl p-2"></p>
    </form>

    <!-- Footer -->
    <div class="text-center mt-4 pt-4 border-t border-gray-600/30">
        <p class="text-xs text-gray-300">
            Não tem conta?
            <button onclick="abrirModal('register')" class="text-green-400 font-bold hover:text-green-3000 transition-colors underline">
                Registre-se aqui
            </button>
        </p>
    </div>
</div>


            <!-- REGISTER - NOVO DESIGN PREMIUM -->
<div id="modalRegister" class="hidden">
    <!-- Adicionando um ID para estilização específica -->
    

    <!-- HOT Badge -->
    <div class="absolute top-0 right-0 z-10">
        <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg animate-pulse">
            🔥 POPULAR
        </div>
    </div>
    
    <!-- Header Premium -->
    <div class="modal-header text-center relative overflow-hidden">
        <!-- Background Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-t-2xl"></div>
        
        <!-- Content -->
        <div class="relative z-10">
            <!-- Icon with Glow Effect -->
            <!-- NOVO ÍCONE DE CADASTRO - COPIE ESTE BLOCO -->
<div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-2xl relative">
    <!-- Efeito de pulsação -->
    <div class="absolute inset-0 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full animate-ping opacity-30"></div>
    <!-- Ícone de Adicionar Usuário -->
    <svg class="w-6 h-6 text-white relative z-10" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 11a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1v-1z"></path>
    </svg>
</div>

            
            <h2 class="text-lg font-black text-white mb-1">🎉 Criar Conta VIP</h2>
            <p class="text-green-300 font-semibold text-xs">Junte-se a +10.000 ganhadores diários!</p>
            
            <!-- Security Badges -->
            <div class="flex justify-center gap-2 mt-3 flex-wrap">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100/20 text-green-300 border border-green-400/30">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    100% Seguro
                </span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100/20 text-blue-300 border border-blue-400/30">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Verificado
                </span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100/20 text-purple-300 border border-purple-400/30">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Instant PIX
                </span>
            </div>
        </div>
    </div>

    <!-- Form Premium -->
    <form id="formRegister" class="space-y-3 mt-4">
        <!-- Nome -->
        <div class="relative">
            <label class="block text-xs font-bold mb-1 text-green-400 flex items-center">
                <i class="fas fa-user mr-1"></i> Nome Completo
            </label>
            <input type="text" name="nome" placeholder="Digite seu nome completo" required 
                   class="form-input w-full rounded-xl bg-gray-800/50 border-2 border-gray-600/50 text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition-all duration-300 pl-4 pr-4 py-3 backdrop-blur-sm">
        </div>

        <!-- Email -->
        <div class="relative">
            <label class="block text-xs font-bold mb-1 text-green-400 flex items-center">
                <i class="fas fa-envelope mr-1"></i> Email
            </label>
            <input type="email" name="email" placeholder="seu@email.com" required 
                   class="form-input w-full rounded-xl bg-gray-800/50 border-2 border-gray-600/50 text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition-all duration-300 pl-4 pr-4 py-3 backdrop-blur-sm">
        </div>

        <!-- Telefone -->
        <div class="relative">
            <label class="block text-xs font-bold mb-1 text-green-400 flex items-center">
                <i class="fas fa-phone mr-1"></i> Telefone (WhatsApp)
            </label>
            <input type="tel" name="telefone" placeholder="(11) 99999-9999" required 
                   class="form-input w-full rounded-xl bg-gray-800/50 border-2 border-gray-600/50 text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition-all duration-300 pl-4 pr-4 py-3 backdrop-blur-sm" 
                   maxlength="15" oninput="formatarTelefone(this)">
        </div>

        <!-- Senha -->
        <div class="relative">
            <label class="block text-xs font-bold mb-1 text-green-400 flex items-center">
                <i class="fas fa-lock mr-1"></i> Senha
            </label>
            <div class="relative">
                <input type="password" id="senha" name="senha" placeholder="Crie uma senha forte (mín. 6 caracteres)" required 
                       class="form-input w-full rounded-xl bg-gray-800/50 border-2 border-gray-600/50 text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition-all duration-300 pl-4 pr-12 py-3 backdrop-blur-sm" minlength="6">
                <button type="button" onclick="togglePassword('senha', 'toggleSenha1')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i id="toggleSenha1" class="fas fa-eye text-gray-400 hover:text-green-400 transition-colors"></i>
                </button>
            </div>
        </div>

        <!-- Confirmação de Senha (condicional) -->
        <?php if ($require_password_confirmation === '1'): ?>
        <div class="relative">
            <label class="block text-xs font-bold mb-1 text-green-400 flex items-center">
                <i class="fas fa-lock mr-1"></i> Confirmar Senha
            </label>
            <div class="relative">
                <input type="password" id="confirmarSenha" name="confirmar_senha" placeholder="Digite a senha novamente" required 
                       class="form-input w-full rounded-xl bg-gray-800/50 border-2 border-gray-600/50 text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition-all duration-300 pl-4 pr-12 py-3 backdrop-blur-sm" minlength="6">
                <button type="button" onclick="togglePassword('confirmarSenha', 'toggleSenha2')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i id="toggleSenha2" class="fas fa-eye text-gray-400 hover:text-green-400 transition-colors"></i>
                </button>
            </div>
            <div id="senhaError" class="text-red-400 text-xs mt-1 hidden">As senhas não conferem</div>
        </div>
        <?php endif; ?>

        <!-- Benefits Banner -->
        <div class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 border border-green-500/30 rounded-xl p-3 my-4">
            <div class="text-center">
                <div class="text-green-400 font-bold text-sm mb-2">🎁 BENEFÍCIOS EXCLUSIVOS:</div>
                <div class="flex justify-center gap-4 text-xs text-green-300">
                    <div class="flex items-center">
                        <i class="fas fa-gift mr-1"></i>
                        <span>Bônus de Boas-vindas</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-bolt mr-1"></i>
                        <span>PIX Instantâneo</span>
                    </div>
                </div>
            </div>
        </div>

     <!-- CHECKBOX MODERNIZADO - SUBSTITUA O BLOCO ANTIGO -->
<!-- Termos -->
<div class="flex items-start space-x-3 mt-2 mb-1">
    <!-- Container do checkbox customizado -->
    <div class="relative flex-shrink-0">
        <!-- Input checkbox oculto -->
        <input type="checkbox" id="termos" required class="sr-only peer">
        
        <!-- Checkbox visual customizado -->
        <label for="termos" class="relative flex items-center justify-center w-5 h-5 bg-gray-800/50 border-2 border-gray-600/50 rounded-lg cursor-pointer transition-all duration-300 hover:border-green-400/70 peer-checked:bg-gradient-to-br peer-checked:from-green-500 peer-checked:to-emerald-600 peer-checked:border-green-400 peer-checked:shadow-lg peer-checked:shadow-green-500/30 peer-focus:ring-2 peer-focus:ring-green-500/20 peer-focus:ring-offset-2 peer-focus:ring-offset-gray-900">
            <!-- Ícone de check -->
            <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            
            <!-- Efeito de brilho quando marcado -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 peer-checked:opacity-100 peer-checked:animate-pulse rounded-lg"></div>
        </label>
    </div>
    
    <!-- Texto dos termos -->
    <label for="termos" class="text-xs text-gray-300 leading-relaxed cursor-pointer hover:text-white transition-colors">
        Eu concordo com os <a href="#" class="text-green-400 hover:text-green-300 underline font-semibold transition-colors">Termos de Uso</a> e 
        <a href="#" class="text-green-400 hover:text-green-300 underline font-semibold transition-colors">Política de Privacidade</a>. 
        <span class="block text-green-400 font-medium">✓ Confirmo que tenho mais de 18 anos.</span>
    </label>
</div>




        <!-- Botão Premium -->
        <button type="submit" class="form-button w-full bg-gradient-to-r from-green-500 via-emerald-500 to-green-600 hover:from-green-600 hover:via-emerald-600 hover:to-green-700 text-white font-black py-4 rounded-xl transition-all transform hover:scale-105 active:scale-95 shadow-2xl relative overflow-hidden text-base">
            <!-- Efeito de brilho -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-full group-hover:translate-x-full transition-transform duration-1000"></div>
            <!-- Conteúdo -->
            <div class="relative z-10 flex items-center justify-center gap-2">
                <i class="fas fa-rocket text-lg"></i>
                <span>CRIAR CONTA E JOGAR AGORA</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </button>

        <!-- Error/Success Messages -->
        <div id="registerMessage" class="hidden text-center p-3 rounded-xl"></div>
    </form>

    <!-- Footer -->
    <div class="text-center mt-4 pt-4 border-t border-gray-600/30">
        <p class="text-xs text-gray-300">
            Já tem conta?
            <button onclick="abrirModal('login')" class="text-green-400 font-bold hover:text-green-300 transition-colors underline">
                Faça login aqui
            </button>
        </p>
    </div>
</div>

<script>
    // Adiciona a classe para compactar em mobile
    function checkMobileCompact() {
        const modalRegister = document.getElementById('modalRegister');
        if (window.innerWidth <= 480) {
            modalRegister.classList.add('compact-mobile');
        } else {
            modalRegister.classList.remove('compact-mobile');
        }
    }
    // Verifica no carregamento e no redimensionamento da tela
    window.addEventListener('DOMContentLoaded', checkMobileCompact);
    window.addEventListener('resize', checkMobileCompact);
</script>
        </div>
    </div>

    <?php if ($usuarioLogado): ?>
        <!-- Modal Depósito (apenas para usuários logados) -->
        <?php if ($usuarioLogado): ?>
    <!-- Modal Depósito (apenas para usuários logados) -->
    <div id="modalDeposito" class="fixed inset-0 bg-black/90 backdrop-blur-sm flex items-center justify-center z-[1001] hidden overflow-y-auto py-4">
        <div class="bg-gradient-to-br from-gray-900 to-black text-white rounded-3xl shadow-2xl w-[95%] md:w-[90%] max-w-md relative transform transition-all border-2 border-green-500 max-h-[90vh] overflow-hidden flex flex-col">
            
            <!-- [INÍCIO DA MODIFICAÇÃO] - Novo Header com Banner -->
            <div class="relative">
                <!-- O banner que você quer adicionar -->
                <img src="img/banner dpix.png" alt="Banner Depósito PIX" class="w-full h-auto object-cover rounded-t-2xl">
                
                <!-- Botão de fechar sobreposto -->
                <button onclick="fecharDeposito()" class="absolute top-3 right-3 text-white/80 hover:text-red-400 font-bold text-xl transition-colors bg-black/30 rounded-full w-8 h-8 flex items-center justify-center z-20">
                    <i class="fas fa-times"></i>
                </button>

                <!-- Gradiente para garantir a legibilidade do texto sobre a imagem -->
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/50 to-transparent rounded-t-2xl"></div>

                <!-- Conteúdo de texto sobreposto na parte inferior do banner -->
                <div class="absolute bottom-0 left-0 p-4">
                    <h2 class="text-xl font-black text-white drop-shadow-lg">💰 DEPÓSITO PIX</h2>
                    <p class="text-white/90 font-medium text-sm drop-shadow-md">Receba seu saldo em segundos!</p>
                </div>
            </div>
            <!-- [FIM DA MODIFICAÇÃO] -->

            <div class="p-4 space-y-4 overflow-y-auto flex-1">
                <!-- Botões de Depósito Rápido -->
                <div class="space-y-2">
                    <h3 class="text-base font-bold text-center text-white mb-2">🚀 Depósito Rápido</h3>
                    <div class="grid grid-cols-3 md:grid-cols-4 gap-2">
                        <button onclick="selecionarValor(20)" class="deposit-quick-btn" data-value="20">R$ 20</button>
                        <button onclick="selecionarValor(30)" class="deposit-quick-btn hot-item" data-value="30">
                            <div class="hot-badge">🔥 HOT</div>
                            R$ 30
                        </button>
                        <button onclick="selecionarValor(50)" class="deposit-quick-btn hot-item" data-value="50">
                            <div class="hot-badge">🔥 HOT</div>
                            R$ 50
                        </button>
                        <button onclick="selecionarValor(100)" class="deposit-quick-btn" data-value="100">R$ 100</button>
                        <button onclick="selecionarValor(150)" class="deposit-quick-btn" data-value="150">R$ 150</button>
                        <button onclick="selecionarValor(200)" class="deposit-quick-btn" data-value="200">R$ 200</button>
                        <button onclick="selecionarValor(300)" class="deposit-quick-btn" data-value="300">R$ 300</button>
                        <button onclick="selecionarValor(500)" class="deposit-quick-btn" data-value="500">R$ 500</button>
                        <button onclick="selecionarValor(1000)" class="deposit-quick-btn vip-item" data-value="1000">
                            <div class="vip-badge">👑 VIP</div>
                            R$ 1000
                        </button>
                    </div>
                </div>

                <!-- Ou inserir valor customizado -->
                <div class="space-y-3">
                    <div class="text-center">
                        <span class="text-gray-400 text-sm font-medium">Ou insira um valor personalizado</span>
                    </div>
                    <label class="block text-sm font-semibold mb-2 text-green-400">💰 Valor do Depósito</label>
                    <input id="valorDeposito" type="number" min="<?= $min_deposit ?>" step="1" placeholder="Ex: <?= (int)$min_deposit ?>" class="w-full bg-gray-800 border-2 border-gray-600 text-white rounded-xl px-3 py-2 text-center text-base font-bold focus:border-green-500 focus:outline-none transition-colors placeholder-gray-500" />
                    <p class="text-xs text-gray-400 mt-1 text-center">💎 Valor mínimo: R$ <?= number_format($min_deposit, 0, ',', '.') ?></p>
                    
                    <?php
                    // Verificar se o depósito em dobro está ativo nas configurações globais
                    $stmt = $conn->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'double_deposit_enabled'");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $doubleDepositEnabled = $result->fetch_assoc()['setting_value'] ?? '0';
                    
                    if ($doubleDepositEnabled == '1'):
                    ?>
                    <!-- Opção de depósito em dobro -->
                    <div class="mt-4 p-3 bg-gradient-to-r from-green-500/10 to-emerald-500/10 border border-green-500/30 rounded-xl">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="doubleDeposit" name="doubleDeposit" value="1" class="form-checkbox h-4 w-4 text-green-500 rounded border-gray-600 focus:ring-green-500 focus:ring-offset-0 transition duration-200">
                            <span class="text-sm text-white font-medium">Ativar bônus de depósito em dobro</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1 ml-6">Receba o mesmo valor depositado como bônus🎉💰</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botão Gerar PIX -->
            <div class="p-4 pt-0">
                <button onclick="gerarPix()" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-105 active:scale-95 shadow-lg text-base flex items-center justify-center gap-2">
                    <i class="fab fa-pix text-2xl"></i>
                    <span>GERAR CÓDIGO PIX</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>

            <div id="resultadoPix" class="mt-4 text-sm"></div>
        </div>
    </div>
<?php endif; ?>

        </div>

        <!-- Modal QR Code (apenas para usuários logados) -->
        <div id="qrMode" class="fixed inset-0 bg-black/95 backdrop-blur-sm flex items-center justify-center z-[1001] hidden overflow-y-auto py-4">
            <div class="bg-gradient-to-br from-gray-900 to-black text-white rounded-3xl shadow-2xl w-[95%] md:w-[90%] max-w-lg relative border-2 border-green-500 max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header com banner dpix -->
                <div class="relative">
                    <img src="img/banner dpix.png" alt="Banner PIX" class="w-full h-20 object-cover rounded-t-3xl" id="bannerDpix" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent rounded-t-3xl"></div>
                    <button onclick="fecharQRMode()" class="absolute top-4 right-4 text-white/80 hover:text-red-400 font-bold text-xl transition-colors bg-black/30 rounded-full w-8 h-8 flex items-center justify-center" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="absolute bottom-2 left-4 right-4">
                        <h2 class="text-xl font-black text-white">📱 ESCANEIE O QR CODE</h2>
                        <p class="text-white/90 text-sm font-medium">Pagamento instantâneo via PIX</p>
                    </div>
                </div>

                <div class="p-6 space-y-6 overflow-y-auto flex-1">
                    <!-- QR Code Section -->
                    <div class="bg-white rounded-2xl p-4 text-center shadow-lg">
                        <img id="qrCodeImage" src="/placeholder.svg" alt="QR Code Pix" class="w-full max-w-xs mx-auto rounded-xl shadow-md" />
                    </div>

                    <!-- Valor do depósito -->
                    <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm rounded-xl p-4 text-center border border-green-500/30">
                        <p class="text-green-400 font-semibold text-sm">💰 VALOR DO DEPÓSITO</p>
                        <p id="valorDepositoQR" class="text-white font-black text-2xl">R$ 0,00</p>
                    </div>

                    <!-- Botões de ação -->
                    <div class="space-y-3">
                        <button id="qrCopyBtn" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-xl transition-all flex items-center justify-center gap-3 transform hover:scale-105 active:scale-95">
                            <i class="fas fa-copy text-lg"></i> 
                            <span>COPIAR CÓDIGO PIX</span>
                        </button>
                        
                        <button id="jaFizPagamento" onclick="window.location.href='inicio'" class="w-full bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white font-bold py-4 px-6 rounded-xl transition-all flex items-center justify-center gap-3 transform hover:scale-105 active:scale-95 opacity-50 cursor-not-allowed" disabled>
                            <i class="fas fa-clock text-lg" id="timerIcon"></i>
                            <span id="timerText">AGUARDE... (17s)</span>
                        </button>
                    </div>

                    <!-- Info de segurança -->
                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="fas fa-shield-check text-green-400 text-lg"></i>
                            <span class="text-green-400 font-bold text-sm">PAGAMENTO SEGURO</span>
                        </div>
                        <p class="text-white/80 text-sm">
                            ⚡ Após o pagamento, o saldo será liberado automaticamente em sua conta.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Botão de Suporte -->
    <a href="<?php echo $suporte_url; ?>" target="_blank" class="suporte-btn">

        <i class="fab fa-telegram-plane text-xl"></i>
    </a>
    

    <!-- Scripts -->
    <script>
        // Mostrar modal de registro automaticamente se não estiver logado
        <?php if (!$usuarioLogado): ?>
        document.addEventListener('DOMContentLoaded', function() {
            abrirModal('register');
        });
        function fecharDeposito() {
    const modalDeposito = document.getElementById('modalDeposito');
    if (modalDeposito) {
        modalDeposito.classList.add('hidden');
    }
    document.body.style.overflow = 'auto';
    // Mostrar botão de suporte novamente
    const suporteBtn = document.querySelector('.suporte-btn');
    if (suporteBtn) {
        suporteBtn.classList.remove('hidden');
    }
}
        <?php endif; ?>

        // Função para atualizar o saldo
        function atualizarSaldo() {
            // Verificar se o usuário está logado antes de tentar atualizar o saldo
            <?php if ($usuarioLogado): ?>
            fetch('get_balance.php')
                .then(response => response.json())
                .then(data => {
                    if (data.saldo) {
                        // Atualizar o saldo na versão desktop
                        const saldoElementDesktop = document.querySelector('.bg-green-500.text-white.px-3.py-1.rounded');
                        if (saldoElementDesktop) {
                            saldoElementDesktop.textContent = `R$ ${data.saldo}`;
                        }
                        
                        // Atualizar o saldo na versão mobile
                        const saldoElementMobile = document.querySelector('.mobile-saldo-display');
                        if (saldoElementMobile) {
                            saldoElementMobile.textContent = `R$ ${data.saldo}`;
                        }
                    }
                })
                .catch(error => console.error('Erro ao atualizar saldo:', error));
            <?php endif; ?>
        }

        // Atualizar saldo quando a página carrega
        document.addEventListener('DOMContentLoaded', atualizarSaldo);

        // Atualizar saldo quando a página se torna visível novamente (após voltar de outra página)
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                atualizarSaldo();
            }
        });

        // Carousel Variables
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.indicator');
        const totalSlides = slides.length;

        // Carousel Functions
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === index);
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }

        function goToSlide(index) {
            currentSlide = index;
            showSlide(currentSlide);
        }

        // Auto-advance carousel every 3 seconds
        setInterval(nextSlide, 3000);

        // Bottom Navigation Functions
        function setActiveNav(element) {
            // Remove active class from all nav items except central button
            document.querySelectorAll('.nav-item').forEach(item => {
                if (!item.classList.contains('central-button')) {
                    item.classList.remove('active');
                }
            });
            // Add active class to clicked item (unless it's the central button)
            if (!element.classList.contains('central-button')) {
                element.classList.add('active');
            }
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Modal Functions
        function abrirModal(tipo) {
            document.getElementById('modalContainer').classList.remove('hidden');
            document.getElementById('modalLogin').classList.add('hidden');
            document.getElementById('modalRegister').classList.add('hidden');
            if (tipo === 'login') document.getElementById('modalLogin').classList.remove('hidden');
            if (tipo === 'register') document.getElementById('modalRegister').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Hide bottom navbar when modal opens
            const bottomNavbar = document.querySelector('.bottom-navbar');
            if (bottomNavbar) {
                bottomNavbar.style.transform = 'translateY(100%)';
                bottomNavbar.style.opacity = '0';
            }
        }

        function fecharModal() {
            document.getElementById('modalContainer').classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Show bottom navbar when modal closes
            const bottomNavbar = document.querySelector('.bottom-navbar');
            if (bottomNavbar) {
                bottomNavbar.style.transform = 'translateY(0)';
                bottomNavbar.style.opacity = '1';
            }
        }

        <?php if ($usuarioLogado): ?>
            // Funções de depósito (apenas para usuários logados)
            function abrirDeposito() {
    document.getElementById('modalDeposito').classList.remove('hidden');
    document.getElementById('resultadoPix').innerHTML = "";
    document.body.style.overflow = 'hidden';
    // Esconder botão de suporte
    const suporteBtn = document.querySelector('.suporte-btn');
    if (suporteBtn) {
        suporteBtn.classList.add('hidden');
    }
}

            function fecharDeposito() {
    document.getElementById('modalDeposito').classList.add('hidden');
    document.body.style.overflow = 'auto';
    // Mostrar botão de suporte novamente
    const suporteBtn = document.querySelector('.suporte-btn');
    if (suporteBtn) {
        suporteBtn.classList.remove('hidden');
    }
}

function abrirQRMode(qrCodeData, pixCode, valor) {
    fecharDeposito();
    document.getElementById('qrCodeImage').src = qrCodeData;
    
    // Atualizar o valor do depósito no modal QR
    const valorFormatado = valor.toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
    document.getElementById('valorDepositoQR').textContent = valorFormatado;
    
    // Redimensionar e animar o banner dpix
    const bannerDpix = document.getElementById('bannerDpix');
    setTimeout(() => {
        bannerDpix.classList.add('resized');
    }, 300);
    
    const copyBtn = document.getElementById('qrCopyBtn');
    copyBtn.onclick = () => copiarPixCode(pixCode);
    
    // Iniciar timer do botão "Já fiz o pagamento" (17 segundos)
    iniciarTimerBotaoPagamento();
    
    document.getElementById('qrMode').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Iniciar verificação periódica
    iniciarVerificacaoPagamento();
    
    // Disparar evento Facebook Pixel InitiateCheckout
    if (typeof fbq !== 'undefined') {
        fbq('track', 'InitiateCheckout');
    }
    
    // Disparar evento Kwai Pixel initiatedCheckout
    if (typeof kwai !== 'undefined' && typeof kwai.track === 'function') {
        kwai.track('initiatedCheckout');
    }
}

// Função para iniciar o timer de 17 segundos do botão "Já fiz o pagamento"
function iniciarTimerBotaoPagamento() {
    const botao = document.getElementById('jaFizPagamento');
    const timerText = document.getElementById('timerText');
    const timerIcon = document.getElementById('timerIcon');
    
    let segundosRestantes = 17;
    
    // Estado inicial - desabilitado
    botao.disabled = true;
    botao.classList.add('timer-active');
    botao.classList.remove('timer-finished');
    timerIcon.className = 'fas fa-clock text-lg';
    timerText.textContent = `AGUARDE... (${segundosRestantes}s)`;
    
    const timerInterval = setInterval(() => {
        segundosRestantes--;
        timerText.textContent = `AGUARDE... (${segundosRestantes}s)`;
        
        if (segundosRestantes <= 0) {
            clearInterval(timerInterval);
            
            // Habilitar o botão
            botao.disabled = false;
            botao.classList.remove('timer-active');
            botao.classList.add('timer-finished');
            timerIcon.className = 'fas fa-check text-lg';
            timerText.textContent = 'JÁ FIZ O PAGAMENTO';
            
            // Efeito de animação quando o botão fica disponível
            botao.style.animation = 'buttonReady 0.8s ease-out';
            
            setTimeout(() => {
                botao.style.animation = '';
            }, 800);
        }
    }, 1000);
    
    // Armazenar o intervalo para poder limpar se necessário
    window.timerPagamentoInterval = timerInterval;
}
            
            // Função para iniciar a verificação periódica do pagamento
            function iniciarVerificacaoPagamento() {
                // Limpar intervalo anterior se existir
                if (verificacaoIntervalo) {
                    clearInterval(verificacaoIntervalo);
                }
                
                // Verificar a cada 3 segundos
                verificacaoIntervalo = setInterval(verificarPagamento, 3000);
            }
            
            // Função para verificar o status do pagamento
            async function verificarPagamento() {
                try {
                    const resposta = await fetch('verificar_deposito.php');
                    const dados = await resposta.json();
                    
                    if (dados.deposito_pago) {
                        // Pagamento foi confirmado
                        clearInterval(verificacaoIntervalo);
                        fecharQRMode();
                        mostrarMensagemSucesso();
                        atualizarSaldoAnimado(dados.novo_saldo);
                    }
                } catch (erro) {
                    console.error("Erro ao verificar pagamento:", erro);
                }
            }
            
            // Função para mostrar mensagem de sucesso
function mostrarMensagemSucesso() {
    // Criar notificação
    const mensagem = document.createElement('div');
    mensagem.style.position = 'fixed';
    mensagem.style.top = '20px';
    mensagem.style.left = '50%';
    mensagem.style.transform = 'translateX(-50%)';
    mensagem.style.backgroundColor = '#22c55e';
    mensagem.style.color = 'white';
    mensagem.style.padding = '15px 25px';
    mensagem.style.borderRadius = '10px';
    mensagem.style.boxShadow = '0 4px 15px rgba(34, 197, 94, 0.3), 0 6px 20px rgba(0, 0, 0, 0.3)';
    mensagem.style.zIndex = '10001';
    mensagem.style.display = 'flex';
    mensagem.style.alignItems = 'center';
    mensagem.style.gap = '10px';
    
    mensagem.innerHTML = `
        <i class="fas fa-check-circle" style="font-size: 20px;"></i>
        <div>
            <p style="font-weight: bold; margin: 0;">Pagamento Confirmado</p>
            <p style="margin: 0;">Seu depósito foi processado com sucesso!</p>
        </div>
    `;
    
    document.body.appendChild(mensagem);
    
    // Disparar evento Facebook Pixel Purchase
    if (typeof fbq !== 'undefined') {
        // Obter o valor do depósito
        const valorDepositoElement = document.getElementById('valorDeposito');
        const valorDeposito = valorDepositoElement ? parseFloat(valorDepositoElement.value) : 0;
        
        // Disparar evento de compra
        fbq('track', 'Purchase', {
            value: valorDeposito,
            currency: 'BRL'
        });
    }
    
    // Disparar evento Kwai Pixel purchase
    if (typeof kwai !== 'undefined' && typeof kwai.track === 'function') {
        // Obter o valor do depósito
        const valorDepositoElement = document.getElementById('valorDeposito');
        const valorDeposito = valorDepositoElement ? parseFloat(valorDepositoElement.value) : 0;
        
        // Disparar evento de compra
        kwai.track('purchase', {
            value: valorDeposito,
            currency: 'BRL'
        });
    }
    
    // Remover após 4 segundos
    setTimeout(() => {
        mensagem.style.opacity = '0';
        mensagem.style.transition = 'opacity 0.5s';
        setTimeout(() => mensagem.remove(), 500);
    }, 4000);
}
            
            // Função para atualizar saldo com animação
            function atualizarSaldoAnimado(novoSaldo) {
                // Elementos que mostram o saldo
                const saldoDesktop = document.querySelector('.bg-green-500.text-white.px-3.py-1.rounded');
                const saldoMobile = document.querySelector('.mobile-saldo-display');
                
                // Animar o saldo desktop
                if (saldoDesktop) {
                    // Guardar cor original
                    const corOriginal = window.getComputedStyle(saldoDesktop).backgroundColor;
                    
                    // Aplicar highlight
                    saldoDesktop.style.backgroundColor = '#16a34a';
                    saldoDesktop.style.transition = 'background-color 1s';
                    saldoDesktop.textContent = `R$ ${novoSaldo}`;
                    
                    // Voltar para cor original após 1 segundo
                    setTimeout(() => {
                        saldoDesktop.style.backgroundColor = corOriginal;
                    }, 1000);
                }
                
                // Animar o saldo mobile
                if (saldoMobile) {
                    const corOriginal = window.getComputedStyle(saldoMobile).backgroundColor;
                    
                    saldoMobile.style.backgroundColor = '#16a34a';
                    saldoMobile.style.transition = 'background-color 1s';
                    saldoMobile.textContent = `R$ ${novoSaldo}`;
                    
                    setTimeout(() => {
                        saldoMobile.style.backgroundColor = corOriginal;
                    }, 1000);
                }
            }

            // Variável para controlar a verificação de pagamento
            let verificacaoIntervalo;
            
            function fecharQRMode() {
    document.getElementById('qrMode').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Parar a verificação quando fechar o modal
    if (verificacaoIntervalo) {
        clearInterval(verificacaoIntervalo);
    }
    
    // Limpar o timer se existir
    if (window.timerPagamentoInterval) {
        clearInterval(window.timerPagamentoInterval);
    }
    
    // Mostrar botão de suporte novamente
    const suporteBtn = document.querySelector('.suporte-btn');
    if (suporteBtn) {
        suporteBtn.classList.remove('hidden');
    }
}

            function copiarPixCode(codigo) {
                navigator.clipboard.writeText(codigo).then(() => {
                    const copyBtn = document.getElementById('qrCopyBtn');
                    const originalText = copyBtn.innerHTML;
                    copyBtn.classList.add('bg-green-600');
                    copyBtn.classList.remove('bg-blue-600');
                    copyBtn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                    setTimeout(() => {
                        copyBtn.classList.remove('bg-green-600');
                        copyBtn.classList.add('bg-blue-600');
                        copyBtn.innerHTML = originalText;
                    }, 2000);
                }).catch(() => {
                    alert("Erro ao copiar o código Pix.");
                });
            }

            // Função para mostrar o popup de erro
            function mostrarErroDeposito(mensagem) {
                // Verificar se já existe um popup de erro e removê-lo
                const erroExistente = document.getElementById('erroDepositoPopup');
                if (erroExistente) {
                    erroExistente.remove();
                }
                
                // Criar o elemento do popup
                const popup = document.createElement('div');
                popup.id = 'erroDepositoPopup';
                popup.style.position = 'fixed';
                popup.style.top = '20px';
                popup.style.left = '50%';
                popup.style.transform = 'translateX(-50%)';
                popup.style.backgroundColor = '#ff5252';
                popup.style.color = 'white';
                popup.style.padding = '15px 25px';
                popup.style.borderRadius = '10px';
                popup.style.boxShadow = '0 4px 15px rgba(255, 82, 82, 0.3)';
                popup.style.zIndex = '10001';
                popup.style.maxWidth = '90%';
                popup.style.textAlign = 'center';
                popup.style.animation = 'slideInDown 0.3s forwards';
                
                // Adicionar ícone
                const conteudo = `
                    <div style="display: flex; align-items: center;">
                        <i class="fas fa-exclamation-circle" style="font-size: 20px; margin-right: 10px;"></i>
                        <div>
                            <div style="font-weight: bold; margin-bottom: 3px;">Valor Insuficiente</div>
                            <div style="font-size: 14px;">${mensagem}</div>
                        </div>
                        <i class="fas fa-times" style="margin-left: 15px; cursor: pointer; font-size: 16px;" onclick="document.getElementById('erroDepositoPopup').remove()"></i>
                    </div>
                `;
                
                popup.innerHTML = conteudo;
                
                // Adicionar ao body
                document.body.appendChild(popup);
                
                // Adicionar estilo de animação
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes slideInDown {
                        from {
                            transform: translate(-50%, -20px);
                            opacity: 0;
                        }
                        to {
                            transform: translate(-50%, 0);
                            opacity: 1;
                        }
                    }
                `;
                document.head.appendChild(style);
                
                // Auto-fechar após 5 segundos
                setTimeout(() => {
                    if (document.getElementById('erroDepositoPopup')) {
                        document.getElementById('erroDepositoPopup').style.animation = 'fadeOut 0.3s forwards';
                        setTimeout(() => {
                            if (document.getElementById('erroDepositoPopup')) {
                                document.getElementById('erroDepositoPopup').remove();
                            }
                        }, 300);
                    }
                }, 5000);
            }
            
            // Função para selecionar valor dos botões de depósito rápido
            function selecionarValor(valor) {
                // Remove seleção anterior
                document.querySelectorAll('.deposit-quick-btn').forEach(btn => {
                    btn.classList.remove('selected');
                });
                
                // Adiciona seleção ao botão clicado
                document.querySelector(`[data-value="${valor}"]`).classList.add('selected');
                
                // Define o valor no input
                document.getElementById('valorDeposito').value = valor;
                
                // Animação de feedback
                const btn = document.querySelector(`[data-value="${valor}"]`);
                btn.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    btn.style.transform = '';
                }, 150);
            }

            async function gerarPix() {
                const valor = parseFloat(document.getElementById('valorDeposito').value);
                const minDeposit = <?= $min_deposit ?>;
                
                // Verificar se o checkbox de depósito em dobro está marcado
                const doubleDepositCheckbox = document.getElementById('doubleDeposit');
                const doubleDeposit = doubleDepositCheckbox && doubleDepositCheckbox.checked ? true : false;
                                
                if (!valor || valor < minDeposit) {
                    mostrarErroDeposito("O valor mínimo para depósito é R$ " + minDeposit.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    return;
                }

                try {
                    const res = await fetch("gerar_pix_bspay.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ valor, doubleDeposit })
                    });

                    const data = await res.json();

                    if (data.erro) {
                        alert(data.erro);
                        return;
                    }

                    const qrPayload = encodeURIComponent(data.qrcode);
                    const imgSrc = `https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=${qrPayload}`;
                    
                    // Armazenar o valor do depósito para usar no modal QR
                    window.valorDepositoAtual = valor;
                    
                    abrirQRMode(imgSrc, data.qrcode, valor);
                } catch (error) {
                    alert('Erro ao gerar código Pix. Tente novamente.');
                }
            }
        <?php endif; ?>

        // Smooth Scroll
        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Close modal when clicking outside
        document.getElementById('modalContainer').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });

        <?php if ($usuarioLogado): ?>
            // Close modals when clicking outside (apenas para usuários logados)
            document.getElementById('modalDeposito')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharDeposito();
                }
            });

            document.getElementById('qrMode')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharQRMode();
                }
            });
        <?php endif; ?>

        // Form Handlers
        document.getElementById('formLogin').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            const errorElement = document.getElementById('loginErro');

            try {
                const res = await fetch('login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                if (result.sucesso) {
                    window.location.reload(); // Recarrega a página para mostrar o usuário logado
                } else {
                    errorElement.textContent = result.erro || 'Erro ao fazer login';
                    errorElement.classList.remove('hidden');
                }
            } catch (err) {
                errorElement.textContent = 'Erro de conexão. Tente novamente.';
                errorElement.classList.remove('hidden');
            }
        });

        document.getElementById('formRegister').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const dados = Object.fromEntries(formData.entries());

            try {
                const res = await fetch("register.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify(dados)
                });

                const resposta = await res.json();

                if (resposta.status === "sucesso") {
                    window.location.reload(); // Recarrega a página para mostrar o usuário logado
                } else {
                    alert(resposta.mensagem || 'Erro ao criar conta');
                }
            } catch (err) {
                alert('Erro de conexão. Tente novamente.');
            }
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-up');
                }
            });
        }, observerOptions);

        const observer2 = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-up');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.card-hover, .stats-counter, .raspadinha-card').forEach(el => {
            observer.observe(el);
        });

        // Observe elements for animation
        document.querySelectorAll('.card-hover, .stats-counter, .raspadinha-card').forEach(el => {
            observer2.observe(el);
        });

        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stats-counter');
            counters.forEach(counter => {
                const target = counter.textContent;
                const isNumber = /^\d+/.test(target);
                                
                if (isNumber) {
                    const finalNumber = parseInt(target.replace(/\D/g, ''));
                    let current = 0;
                    const increment = finalNumber / 50;
                                        
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= finalNumber) {
                            current = finalNumber;
                            clearInterval(timer);
                        }
                        counter.textContent = target.replace(/\d+/, Math.floor(current));
                    }, 30);
                }
            });
        }

        // Start counter animation when page loads
        window.addEventListener('load', () => {
            setTimeout(animateCounters, 500);
        });

        // Função para formatar telefone brasileiro (celular)
        function formatarTelefone(input) {
            // Remove tudo que não for número
            let valor = input.value.replace(/\D/g, '');
            
            // Limita a 11 dígitos (DDD + 9 dígitos do celular)
            if (valor.length > 11) {
                valor = valor.slice(0, 11);
            }
            
            // Aplica a formatação
            if (valor.length >= 11) {
                // Formato: (11) 99999-9999
                valor = valor.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (valor.length >= 7) {
                // Formato: (11) 9999-9999
                valor = valor.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (valor.length >= 3) {
                // Formato: (11) 999
                valor = valor.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            } else if (valor.length >= 1) {
                // Formato: (1
                valor = valor.replace(/(\d{0,2})/, '($1');
            }
            
            input.value = valor;
        }

        // Função para alternar visibilidade da senha
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Validação de confirmação de senha em tempo real (se habilitada)
        <?php if ($require_password_confirmation === '1'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const senhaInput = document.getElementById('senha');
            const confirmarSenhaInput = document.getElementById('confirmarSenha');
            const senhaError = document.getElementById('senhaError');
            
            function validarSenhas() {
                if (confirmarSenhaInput.value && senhaInput.value !== confirmarSenhaInput.value) {
                    senhaError.classList.remove('hidden');
                    confirmarSenhaInput.style.borderColor = '#ef4444';
                    return false;
                } else {
                    senhaError.classList.add('hidden');
                    confirmarSenhaInput.style.borderColor = '';
                    return true;
                }
            }
            
            if (senhaInput && confirmarSenhaInput) {
                senhaInput.addEventListener('input', validarSenhas);
                confirmarSenhaInput.addEventListener('input', validarSenhas);
                
                // Validar antes do submit
                document.getElementById('formRegister').addEventListener('submit', function(e) {
                    if (!validarSenhas()) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
        <?php endif; ?>

        // Função para mostrar mensagens no modal de registro
        function mostrarMensagemRegistro(mensagem, tipo = 'error') {
            const messageElement = document.getElementById('registerMessage');
            if (messageElement) {
                messageElement.textContent = mensagem;
                messageElement.className = `text-center p-3 rounded-xl text-sm font-medium ${tipo === 'error' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-green-500/20 text-green-400 border border-green-500/30'}`;
                messageElement.classList.remove('hidden');
                
                // Auto-hide após 5 segundos
                setTimeout(() => {
                    messageElement.classList.add('hidden');
                }, 5000);
            }
        }


    </script>
</body>
</html>
