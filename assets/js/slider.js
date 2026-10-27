(() => {
  window.initHeroSlider = (root) => {
    root._sliderCleanup?.();
    const images = [...root.querySelectorAll('.slider-image')];
    const dots = [...root.querySelectorAll('[data-slider-dot]')];
    const pause = root.querySelector('[data-slider-pause]');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    const abort = new AbortController();
    let index = 0, timer, paused = motion.matches, start = null;
    const listen = (element, event, handler) => element?.addEventListener(event, handler, {signal:abort.signal});
    const stop = () => clearInterval(timer);
    const show = (next, announce = true) => {
      index = (next + images.length) % images.length;
      images.forEach((img,i) => { img.classList.toggle('is-active',i === index); img.setAttribute('aria-hidden',String(i !== index)); if (i === index) img.loading = 'eager'; });
      dots.forEach((dot,i) => dot.setAttribute('aria-pressed',String(i === index)));
      if (announce) root.querySelector('[data-slider-status]').textContent = `Bild ${index + 1} von ${images.length}`;
    };
    const schedule = () => {
      stop();
      if (root.dataset.autoplay === 'true' && !paused && !motion.matches && images.length > 1 && !document.hidden && !root.matches(':hover') && !root.contains(document.activeElement)) timer = setInterval(() => show(index + 1,false), Number(root.dataset.interval) * 1000);
    };
    const move = (delta) => { show(index + delta); schedule(); };
    listen(root.querySelector('[data-slider-prev]'),'click',() => move(-1));
    listen(root.querySelector('[data-slider-next]'),'click',() => move(1));
    dots.forEach((dot,i) => listen(dot,'click',() => { show(i); schedule(); }));
    listen(root,'keydown',(event) => { if (!['ArrowLeft','ArrowRight'].includes(event.key) || /INPUT|TEXTAREA|SELECT/.test(event.target.tagName)) return; event.preventDefault(); move(event.key === 'ArrowRight' ? 1 : -1); });
    listen(root,'pointerdown',(event) => { if (event.pointerType === 'touch' && !event.target.closest('a,button')) start = {x:event.clientX,y:event.clientY}; });
    listen(root,'pointerup',(event) => { if (!start) return; const x=event.clientX-start.x,y=event.clientY-start.y; start=null; if (Math.abs(x)>50 && Math.abs(x)>Math.abs(y)*1.5) move(x<0?1:-1); });
    listen(root,'pointercancel',() => { start=null; });
    listen(pause,'click',() => { paused=!paused; pause.textContent=paused?'Automatik starten':'Automatik pausieren'; schedule(); });
    if (motion.matches && pause) { pause.hidden=true; }
    listen(motion,'change',() => { paused=motion.matches; if(pause) pause.hidden=motion.matches || root.dataset.autoplay !== 'true'; schedule(); });
    listen(root,'mouseenter',stop); listen(root,'mouseleave',schedule);
    listen(root,'focusin',stop); listen(root,'focusout',() => setTimeout(schedule,0));
    listen(document,'visibilitychange',schedule);
    images.forEach((img) => listen(img,'error',() => { if (!img.dataset.fallback) { img.dataset.fallback='true'; img.src='brand/rent-a-bau-hero.png'; } }));
    root._sliderCleanup=() => { stop(); abort.abort(); };
    show(0,false); schedule();
  };
  document.querySelectorAll('[data-hero-slider]').forEach(window.initHeroSlider);
})();
