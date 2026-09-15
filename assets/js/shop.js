(function(){
  const root=document.documentElement;
  const themeBtn=document.getElementById('shopThemeToggle');
  const menuBtn=document.getElementById('mobileMenuToggle');
  const mobileMenu=document.getElementById('mobileMenuPanel');

  function setThemeIcon(){
    if(!themeBtn) return;
    const dark=root.classList.contains('shop-theme-dark');
    if(document.body) document.body.classList.toggle('shop-theme-dark', dark);
    themeBtn.innerHTML = dark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-stars-fill"></i>';
    themeBtn.title=dark?'Mode terang':'Mode gelap';
    themeBtn.setAttribute('aria-label',dark?'Ubah ke mode terang':'Ubah ke mode gelap');
  }

  if(themeBtn){
    themeBtn.addEventListener('click',function(){
      root.classList.toggle('shop-theme-dark');
      try{localStorage.setItem('hanatabatik_shop_theme',root.classList.contains('shop-theme-dark')?'dark':'light');}catch(e){}
      setThemeIcon();
    });
    setThemeIcon();
  }

  function toggleMenu(force){
    if(!menuBtn || !mobileMenu) return;
    const open=typeof force==='boolean'?force:!mobileMenu.classList.contains('open');
    mobileMenu.classList.toggle('open',open);
    menuBtn.classList.toggle('open',open);
    menuBtn.setAttribute('aria-expanded',open?'true':'false');
  }

  if(menuBtn && mobileMenu){
    menuBtn.addEventListener('click',()=>toggleMenu());
    mobileMenu.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>toggleMenu(false)));
    document.addEventListener('click',function(e){
      if(!mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) toggleMenu(false);
    });
    window.addEventListener('resize',function(){ if(window.innerWidth>980) toggleMenu(false); });
  }

  // Fan/stack hero: clicking the front card sends it to the back.
  const stack=document.querySelector('[data-hero-stack]');
  if(stack){
    const cards=Array.from(stack.querySelectorAll('.hero-slide'));
    let busy=false;
    function orderCards(){
      cards.forEach((card,i)=>{
        const position=cards.indexOf(card);
        card.style.setProperty('--stack-index',cards.length-position);
        card.classList.toggle('is-front',position===0);
        card.setAttribute('aria-hidden',position===0?'false':'true');
        card.style.setProperty('--stack-y',(position*10)+'px');
        card.style.setProperty('--stack-x',(position*10)+'px');
        card.style.setProperty('--stack-r',(position===0?'0':(position%2===0?'-3deg':'3deg')));
      });
    }
    function rotateStack(card){
      if(busy) return;
      busy=true;
      card.classList.add('is-sweeping');
      setTimeout(()=>{
        card.classList.remove('is-sweeping');
        cards.push(cards.shift());
        orderCards();
        busy=false;
      },420);
    }
    cards.forEach(card=>card.addEventListener('click',()=>rotateStack(card)));
    orderCards();
  }

  // Full-width news banner: swipe/click between stories and open translucent detail modal.
  const newsSlider=document.querySelector('[data-news-slider]');
  if(newsSlider){
    const slides=Array.from(newsSlider.querySelectorAll('[data-news-slide]'));
    const dots=Array.from(newsSlider.querySelectorAll('[data-news-dot]'));
    const prev=newsSlider.querySelector('[data-news-prev]');
    const next=newsSlider.querySelector('[data-news-next]');
    const modal=document.querySelector('[data-news-modal]');
    const modalTitle=document.getElementById('newsModalTitle');
    const modalMeta=document.getElementById('newsModalMeta');
    const modalDescription=document.getElementById('newsModalDescription');
    let current=0;

    function showNews(index){
      if(!slides.length) return;
      current=(index+slides.length)%slides.length;
      slides.forEach((slide,i)=>slide.classList.toggle('is-active',i===current));
      dots.forEach((dot,i)=>dot.classList.toggle('active',i===current));
    }
    function openNews(slide){
      if(!modal || !slide) return;
      modalTitle.textContent=slide.dataset.title || '';
      modalMeta.textContent=((slide.dataset.category || 'Berita') + (slide.dataset.date ? ' • '+slide.dataset.date : ''));
      modalDescription.textContent=slide.dataset.description || '';
      modal.classList.add('open');
      modal.setAttribute('aria-hidden','false');
      document.body.classList.add('news-modal-open');
    }
    function closeNews(){
      if(!modal) return;
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden','true');
      document.body.classList.remove('news-modal-open');
    }
    slides.forEach((slide,i)=>slide.addEventListener('click',()=>openNews(slide)));
    dots.forEach((dot,i)=>dot.addEventListener('click',()=>showNews(i)));
    if(prev) prev.addEventListener('click',e=>{e.stopPropagation();showNews(current-1)});
    if(next) next.addEventListener('click',e=>{e.stopPropagation();showNews(current+1)});
    if(modal) modal.querySelectorAll('[data-news-close]').forEach(el=>el.addEventListener('click',closeNews));
    document.addEventListener('keydown',e=>{
      if(!modal || !modal.classList.contains('open')) return;
      if(e.key==='Escape') closeNews();
      if(e.key==='ArrowLeft') showNews(current-1);
      if(e.key==='ArrowRight') showNews(current+1);
    });
    showNews(0);
  }

})();
