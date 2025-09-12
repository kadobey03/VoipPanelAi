// Chart.js örnek grafik ve tema geçişi
const ctx = document.getElementById('chart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs'],
        datasets: [{
            label: 'Arama Sayısı',
            data: [12, 19, 3, 5, 2],
            backgroundColor: 'rgba(59, 130, 246, 0.5)'
        }]
    },
    options: {responsive: true}
});
// Tema geçişi
const btn = document.getElementById('theme-toggle');
btn.addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
});
