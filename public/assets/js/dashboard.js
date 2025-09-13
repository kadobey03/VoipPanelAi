// Safe dashboard helpers
(function(){
  try{
    var el = document.getElementById('chart');
    if (el && window.Chart) {
      var ctx = el.getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: { labels: ['Ocak','Şubat','Mart','Nisan','Mayıs'], datasets:[{ label:'Arama Sayısı', data:[12,19,3,5,2], backgroundColor:'rgba(59,130,246,0.5)'}] },
        options: { responsive:true }
      });
    }
    var btn = document.getElementById('theme-toggle');
    if (btn) btn.addEventListener('click', function(){ document.documentElement.classList.toggle('dark'); });
  }catch(e){}
})();

