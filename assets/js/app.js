/* Quinto Lumière — interacciones de la tienda */
(function () {
  'use strict';
  var QL = window.QL || { base: '', csrf: '' };
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  function safe(fn, name) { try { fn(); } catch (e) { if (window.console) console.warn('[QL] ' + name, e); } }
  function url(p) { return QL.base + '/' + p.replace(/^\//, ''); }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function track(name, params) { if (QL.ga && typeof window.gtag === 'function') window.gtag('event', name, params || {}); }

  function toast(html) {
    var t = $('.toast');
    if (!t) { t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role', 'status'); document.body.appendChild(t); }
    t.innerHTML = html;
    t.classList.add('is-on');
    clearTimeout(t._h);
    t._h = setTimeout(function () { t.classList.remove('is-on'); }, 3200);
  }

  function post(path, data) {
    var body = new FormData();
    Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
    body.append('csrf', QL.csrf);
    return fetch(url(path), { method: 'POST', body: body, credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        // si la sesión se renovó, el servidor manda un token nuevo: reintentamos una vez
        if (d && d.csrf && d.error && !data.__retry) {
          QL.csrf = d.csrf;
          $$('input[name=csrf]').forEach(function (i) { i.value = d.csrf; });
          var again = {}; Object.keys(data).forEach(function (k) { again[k] = data[k]; }); again.__retry = 1;
          return post(path, again);
        }
        return d;
      });
  }

  /* ---------- eventos de Analytics enviados por el servidor ---------- */
  safe(function () {
    (window.QL_EVENTS || []).forEach(function (ev) { track(ev[0], ev[1]); });
  }, 'ga');

  /* ---------- menú móvil, menú de usuario, toggles ---------- */
  safe(function () {
    var btn = $('[data-nav-toggle]'), nav = $('[data-nav]');
    if (btn && nav) btn.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open);
    });
    var um = $('[data-usermenu]');
    if (um) {
      var panel = um.nextElementSibling;
      um.addEventListener('click', function (e) {
        e.stopPropagation();
        panel.hidden = !panel.hidden;
        um.setAttribute('aria-expanded', !panel.hidden);
      });
      document.addEventListener('click', function (e) { if (!panel.contains(e.target)) { panel.hidden = true; um.setAttribute('aria-expanded', 'false'); } });
    }
    $$('[data-toggle]').forEach(function (b) {
      b.addEventListener('click', function () { var t = $(b.getAttribute('data-toggle')); if (t) t.classList.toggle('is-open'); });
    });
    $$('form[data-confirm]').forEach(function (f) {
      f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
    });
  }, 'nav');

  /* ---------- cantidad +/- ---------- */
  safe(function () {
    $$('[data-qty]').forEach(function (b) {
      b.addEventListener('click', function () {
        var input = b.parentNode.querySelector('input');
        var min = +input.min || 0, max = +input.max || 999;
        var v = Math.min(max, Math.max(min, (+input.value || 0) + (+b.getAttribute('data-qty'))));
        if (v === +input.value) return;
        input.value = v;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
    $$('[data-autosubmit]').forEach(function (i) {
      var t;
      i.addEventListener('change', function () { clearTimeout(t); t = setTimeout(function () { i.form.submit(); }, 350); });
    });
  }, 'qty');

  /* ---------- ficha de producto ---------- */
  safe(function () {
    var form = $('[data-add-to-cart]');
    if (!form) return;
    var priceEl = $('[data-price]'), note = $('[data-stocknote]'), qty = form.querySelector('input[name=qty]');
    var submit = form.querySelector('button[type=submit]');

    function update() {
      var r = form.querySelector('input[name=variant]:checked');
      if (!r) { note.textContent = 'Agotado por ahora.'; return; }
      var stock = +r.getAttribute('data-stock');
      priceEl.innerHTML = esc(r.getAttribute('data-price')) + (r.getAttribute('data-compare') ? '<s>' + esc(r.getAttribute('data-compare')) + '</s>' : '');
      qty.max = Math.max(1, stock);
      if (+qty.value > stock) qty.value = Math.max(1, stock);
      note.className = 'stocknote' + (stock > 0 && stock <= 3 ? ' low' : '');
      note.textContent = stock <= 0 ? 'Agotado' : stock <= 3 ? '¡Últimas ' + stock + ' unidades!' : 'Disponible';
      submit.disabled = stock <= 0;
      var picked = $('[data-tonepicked]');
      if (picked && r.getAttribute('data-hex')) {
        picked.querySelector('.sw').style.setProperty('--c', r.getAttribute('data-hex'));
        picked.querySelector('[data-tonename]').textContent = r.getAttribute('data-label');
        var link = $('[data-chart-link]');
        var code = (r.getAttribute('data-label').match(/^\s*(\d{1,2}[.,\/]\d{1,2})/) || [])[1];
        if (link && code) {
          var u = new URL(link.href, location.href);
          u.searchParams.set('tono', code.replace(/[,\/]/, '.'));
          link.href = u.toString();
        }
      }
    }
    $$('input[name=variant]', form).forEach(function (r) { r.addEventListener('change', update); });
    update();

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var r = form.querySelector('input[name=variant]:checked');
      if (!r) return;
      submit.disabled = true;
      post('api/cart.php', { action: 'add', variant: r.value, qty: qty.value })
        .then(function (res) {
          if (res.error) { toast(esc(res.error)); return; }
          var c = $('[data-cart-count]');
          if (c) { c.hidden = false; c.textContent = res.count; c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump'); }
          toast(esc(res.message || 'Agregado') + ' · <a href="' + url('carrito.php') + '">Ver carrito</a>');
          var item = JSON.parse(form.getAttribute('data-product') || '{}');
          item.item_variant = r.getAttribute('data-label');
          item.price = +r.getAttribute('data-raw-price');
          item.quantity = +qty.value;
          item.item_id = r.value;
          track('add_to_cart', { currency: 'PEN', value: item.price * item.quantity, items: [item] });
        })
        .catch(function () { form.submit(); })
        .finally(function () { submit.disabled = false; update(); });
    });

    var main = $('[data-gallery-main]');
    $$('[data-gallery-thumb]').forEach(function (b) {
      b.addEventListener('click', function () {
        main.src = b.getAttribute('data-gallery-thumb');
        $$('[data-gallery-thumb]').forEach(function (x) { x.classList.toggle('is-on', x === b); });
      });
    });
  }, 'pdp');

  /* ---------- carta de colores ---------- */
  safe(function () {
    var f = $('[data-chartform]');
    if (!f) return;
    var pick = $('[data-chartpick]', f);
    $$('input[name=tono]', f).forEach(function (r) {
      r.addEventListener('change', function () {
        pick.innerHTML = '<span class="sw" style="--c:' + esc(r.getAttribute('data-hex')) + '"></span><span><b>' + esc(r.value) + '</b> ' + esc(r.getAttribute('data-name')) + '</span>';
        track('select_content', { content_type: 'tono', content_id: r.value });
      });
    });
  }, 'chart');

  /* agregar al carrito rápido (carta de colores) */
  safe(function () {
    $$('form[data-quick-add]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('button');
        btn.disabled = true;
        post('api/cart.php', { action: 'add', variant: form.variant.value, qty: 1 })
          .then(function (res) {
            if (res.error) { toast(esc(res.error)); return; }
            var c = $('[data-cart-count]');
            if (c) { c.hidden = false; c.textContent = res.count; c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump'); }
            toast(esc(res.message || 'Agregado') + ' · <a href="' + url('carrito.php') + '">Ver carrito</a>');
            track('add_to_cart', { currency: 'PEN', items: [{ item_id: form.variant.value, quantity: 1 }] });
          })
          .catch(function () { form.submit(); })
          .finally(function () { btn.disabled = false; });
      });
    });
  }, 'quickadd');

  /* ---------- checkout: costo de envío en vivo ---------- */
  safe(function () {
    var f = $('[data-checkout]');
    if (!f) return;
    var sub = +f.getAttribute('data-subtotal'), free = +f.getAttribute('data-free');
    var ship = JSON.parse(f.getAttribute('data-ship'));
    var addr = $('[data-address]', f);
    function fmt(n) { return 'S/ ' + n.toFixed(2); }
    function update() {
      var m = (f.querySelector('input[name=ship_method]:checked') || {}).value || 'lima';
      var cost = m === 'recojo' ? ship.recojo : (free > 0 && sub >= free ? 0 : ship[m]);
      $('[data-ship-cost]', f).textContent = cost === 0 ? 'Gratis' : fmt(cost);
      $('[data-total]', f).textContent = fmt(sub + cost);
      addr.hidden = m === 'recojo';
      $$('input', addr).forEach(function (i) { if (i.name !== 'ship_ref') i.required = m !== 'recojo'; });
    }
    $$('input[name=ship_method]', f).forEach(function (r) { r.addEventListener('change', update); });
    update();
    f.addEventListener('submit', function () {
      var pay = (f.querySelector('input[name=pay]:checked') || {}).value;
      track('add_payment_info', { currency: 'PEN', value: sub, payment_type: pay });
    });
  }, 'checkout');

  /* ---------- admin: filas de variantes ---------- */
  safe(function () {
    var body = $('[data-variants]');
    if (!body) return;
    var idx = body.children.length + 100;
    $('[data-add-variant]').addEventListener('click', function () {
      var tr = body.lastElementChild.cloneNode(true);
      $$('input', tr).forEach(function (i) {
        i.name = i.name.replace(/variants\[\d+\]/, 'variants[' + idx + ']');
        i.value = i.type === 'number' ? '0' : '';
      });
      idx++;
      body.appendChild(tr);
      tr.querySelector('input:not([type=hidden])').focus();
    });
    body.addEventListener('click', function (e) {
      var b = e.target.closest('[data-remove-row]');
      if (!b) return;
      if (body.children.length === 1) { alert('El producto necesita al menos una variante.'); return; }
      b.closest('tr').remove();
    });
  }, 'variants');

  /* ---------- aparición suave al hacer scroll ---------- */
  safe(function () {
    var els = $$('.reveal');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) { els.forEach(function (el) { el.classList.add('is-in'); }); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } });
    }, { threshold: 0.02, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function (el, i) { el.style.transitionDelay = Math.min(i % 6, 5) * 50 + 'ms'; io.observe(el); });
    setTimeout(function () { els.forEach(function (el) { el.classList.add('is-in'); }); }, 4000); // red de seguridad
  }, 'reveal');

  /* ---------- chatbot Lumi ---------- */
  safe(function () {
    var root = $('[data-chat]');
    if (!root) return;
    var panel = $('#chat-panel'), log = $('[data-chat-log]'), form = $('[data-chat-form]'), input = form.querySelector('input');
    var fab = $('.chat__fab', root);
    var KEY = 'ql_chat_v1';
    var history = [];
    try { history = JSON.parse(sessionStorage.getItem(KEY) || '[]'); } catch (e) { history = []; }

    function save() { try { sessionStorage.setItem(KEY, JSON.stringify(history.slice(-40))); } catch (e) {} }

    function renderBot(d) {
      var el = document.createElement('div');
      el.className = 'msg msg--bot';
      var html = String(d.text || '').split('\n').map(function (l) { return '<p>' + esc(l) + '</p>'; }).join('');
      if (d.products && d.products.length) {
        html += '<div class="chat__products">' + d.products.map(function (p) {
          return '<a class="chat__prod" href="' + esc(p.url) + '" data-chat-product="' + esc(p.name) + '"><img src="' + esc(p.img) + '" alt="" loading="lazy">' +
            '<span><b>' + esc(p.name) + '</b><small>' + esc(p.price) + (p.stock ? '' : ' · Agotado') + '</small></span></a>';
        }).join('') + '</div>';
      }
      if (d.link) html += '<p style="margin-top:8px"><a href="' + esc(d.link.url) + '" target="_blank" rel="noopener" style="color:var(--rose-2)">' + esc(d.link.label) + ' →</a></p>';
      if (d.chips && d.chips.length) html += '<div class="chat__chips">' + d.chips.map(function (c) { return '<button type="button" data-chip>' + esc(c) + '</button>'; }).join('') + '</div>';
      el.innerHTML = html;
      log.appendChild(el);
    }
    function renderMe(t) {
      var el = document.createElement('div');
      el.className = 'msg msg--me';
      el.textContent = t;
      log.appendChild(el);
    }
    function scroll() { log.scrollTop = log.scrollHeight; }
    function add(kind, data) {
      history.push([kind, data]); save();
      if (kind === 'me') renderMe(data); else renderBot(data);
      scroll();
    }

    function send(text, silent) {
      if (!silent) add('me', text);
      var typing = document.createElement('div');
      typing.className = 'msg msg--bot msg--typing';
      typing.innerHTML = '<span></span><span></span><span></span>';
      log.appendChild(typing); scroll();
      if (!silent) track('chatbot_message', { search_term: text.slice(0, 100) });
      post('api/lumi.php', { message: text }) // (InfinityFree bloquea archivos llamados chat.php)
        .then(function (d) {
          setTimeout(function () {
            typing.remove();
            add('bot', d);
            if (d.followup) setTimeout(function () { add('bot', d.followup); }, 500);
          }, 350);
        })
        .catch(function () {
          typing.remove();
          add('bot', { text: 'Uy, perdí la conexión. Intenta de nuevo en un momento 🙏' });
        });
    }

    function open() {
      root.classList.add('is-open');
      panel.hidden = false;
      $$('[data-chat-toggle]').forEach(function (b) { b.setAttribute('aria-expanded', 'true'); });
      if (!history.length) send('__hola__', true);
      setTimeout(function () { input.focus(); }, 50);
      track('chatbot_open');
    }
    function close() {
      root.classList.remove('is-open');
      panel.hidden = true;
      $$('[data-chat-toggle]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
      fab.focus();
    }

    history.forEach(function (h) { if (h[0] === 'me') renderMe(h[1]); else renderBot(h[1]); });

    $$('[data-chat-toggle]').forEach(function (b) { b.addEventListener('click', function () { panel.hidden ? open() : close(); }); });
    $$('[data-chat-open]').forEach(function (b) { b.addEventListener('click', open); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) close(); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var t = input.value.trim();
      if (!t) return;
      input.value = '';
      send(t);
    });
    log.addEventListener('click', function (e) {
      var chip = e.target.closest('[data-chip]');
      if (chip) { send(chip.textContent); return; }
      var prod = e.target.closest('[data-chat-product]');
      if (prod) track('select_item', { item_list_name: 'Chatbot', items: [{ item_name: prod.getAttribute('data-chat-product') }] });
    });
    try { if (sessionStorage.getItem('ql_chat_open') === '1') open(); } catch (e) {}
    window.addEventListener('pagehide', function () { try { sessionStorage.setItem('ql_chat_open', panel.hidden ? '0' : '1'); } catch (e) {} });
  }, 'chat');
})();
