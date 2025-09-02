<div class="mobile-nav fixed bottom-2 left-2 right-2 px-2 h-[72px] flex items-center gap-x-2.5 md:hidden z-50 rounded-xl shadow-lg">
    <!-- Adicionar saldo móvel invisível para atualização por JS -->
    <span id="saldoMobile" class="hidden">R$ <?= isset($saldo_total) ? number_format($saldo_total, 2, ',', '.') : (isset($saldo) ? number_format($saldo, 2, ',', '.') : '0,00') ?></span>
    <!-- Início -->
    <button onclick="window.location.href='/inicio.php'" class="group flex flex-col items-center justify-center gap-1 text-center select-none flex-1 transition-transform active:scale-90">
        <i class="fas fa-home text-[1.1rem]"></i>
        <span class="text-[0.7rem] font-medium">Início</span>
    </button>

    <!-- Prêmios -->
    <button onclick="window.location.href='/premios'" class="group flex flex-col items-center justify-center gap-1 text-center select-none flex-1 transition-transform active:scale-90">
        <i class="fas fa-trophy text-[1.1rem]"></i>
        <span class="text-[0.7rem] font-medium">Prêmios</span>
    </button>

    <!-- Depositar (levemente elevado) -->
    <button onclick="(window.abrirDeposito ? abrirDeposito() : (window.location.href='/inicio.php#deposito'))" class="group flex flex-col items-center justify-center gap-1 text-center select-none flex-1 transition-transform active:scale-90 -translate-y-[1.25rem]">
        <div class="mobile-nav-deposit rounded-full border-4 border-surface text-white p-3">
            <i class="fas fa-plus text-[1.3rem]"></i>
        </div>
        <span class="text-[0.7rem] font-medium">Depositar</span>
    </button>

    <!-- Indique -->
    <button onclick="window.location.href='/affiliate_dashboard.php'" class="group flex flex-col items-center justify-center gap-1 text-center select-none flex-1 transition-transform active:scale-90">
        <i class="fas fa-users text-[1.1rem]"></i>
        <span class="text-[0.7rem] font-medium">Indique</span>
    </button>

    <!-- Perfil -->
    <button onclick="window.location.href='/perfil.php'" class="group flex flex-col items-center justify-center gap-1 text-center select-none flex-1 transition-transform active:scale-90">
        <i class="fas fa-user text-[1.1rem]"></i>
        <span class="text-[0.7rem] font-medium">Perfil</span>
    </button>
</div>

<script>
(function() {
  function ensureMobileNavFloating() {
    try {
      var nav = document.querySelector('.mobile-nav');
      if (!nav) return;
      if (nav.parentElement !== document.body) {
        document.body.appendChild(nav);
      }
      nav.style.position = 'fixed';
      nav.style.bottom = 'calc(env(safe-area-inset-bottom, 0px) + 0.5rem)';
      nav.style.left = '0.5rem';
      nav.style.right = '0.5rem';
      nav.style.zIndex = '50'; // Reduzido de 2000 para 50 para evitar conflitos
      nav.style.transform = 'none';
      nav.style.width = 'calc(100% - 1rem)';
    } catch (e) {}
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ensureMobileNavFloating);
  } else {
    ensureMobileNavFloating();
  }

  window.addEventListener('resize', ensureMobileNavFloating, { passive: true });
  window.addEventListener('orientationchange', ensureMobileNavFloating, { passive: true });
  window.addEventListener('scroll', function() {
    var nav = document.querySelector('.mobile-nav');
    if (!nav) return;
    if (getComputedStyle(nav).position !== 'fixed') {
      ensureMobileNavFloating();
    }
  }, { passive: true });
})();
</script>
