// ============================================================
// SSC System — Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

  // ---- Sidebar Toggle (Mobile) ----
  const toggleBtn = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  
  if (toggleBtn && sidebar) {
    const toggleSidebar = () => {
      sidebar.classList.toggle('open');
      if (overlay) overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
    };

    toggleBtn.addEventListener('click', toggleSidebar);
    
    if (overlay) {
      overlay.addEventListener('click', toggleSidebar);
    }

    // Auto-close on resize if desktop
    window.addEventListener('resize', () => {
      if (window.innerWidth > 768 && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        if (overlay) overlay.style.display = 'none';
      }
    });
  }

  // ---- Active nav link ----
  const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
  navLinks.forEach(link => {
    if (link.href === window.location.href) {
      link.classList.add('active');
    }
  });

  // ---- Auto-dismiss alerts after 5s ----
  document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 300);
    }, 5000);
  });

  // ---- Animate stat cards on load ----
  document.querySelectorAll('.stat-card').forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(16px)';
    setTimeout(() => {
      card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, i * 80);
  });

  // ---- Animate budget progress bars ----
  document.querySelectorAll('.budget-bar-fill').forEach(bar => {
    const target = bar.getAttribute('data-width') || '0';
    bar.style.width = '0%';
    setTimeout(() => { bar.style.width = target + '%'; }, 300);
  });

  // ---- File upload preview ----
  const fileInput = document.getElementById('receiptFile');
  const previewEl = document.getElementById('filePreview');
  if (fileInput && previewEl) {
    fileInput.addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;
      previewEl.textContent = '📎 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
      previewEl.style.display = 'block';
    });
  }

  // ---- Confirm delete ----
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      if (!confirm(this.getAttribute('data-confirm') || 'Are you sure?')) {
        e.preventDefault();
      }
    });
  });

  // ---- Number counter animation ----
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseFloat(el.getAttribute('data-count').replace(/[^0-9.]/g, ''));
    const prefix = el.getAttribute('data-prefix') || '';
    const isCurrency = el.getAttribute('data-currency') === '1';
    let start = 0;
    const duration = 1200;
    const step = target / (duration / 16);
    const timer = setInterval(() => {
      start = Math.min(start + step, target);
      if (isCurrency) {
        el.textContent = prefix + '₱' + start.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      } else {
        el.textContent = prefix + Math.floor(start).toLocaleString();
      }
      if (start >= target) clearInterval(timer);
    }, 16);
  });

  // ---- Global Input Validation ----
  document.addEventListener('input', function (e) {
    if (e.target.tagName !== 'INPUT') return;

    const name = (e.target.name || e.target.id || '').toLowerCase();
    const type = e.target.type;

    // Auto-format Student ID (YYYY-XXXX)
    if (name.includes('student_id')) {
      let val = e.target.value.replace(/\D/g, ''); // strip all non-digits
      val = val.substring(0, 8); // restrict to 8 digits max
      if (val.length > 4) {
        val = val.substring(0, 4) + '-' + val.substring(4);
      }
      if (e.target.value !== val) {
        e.target.value = val;
      }
      return;
    }

    // Exclude fields that naturally contain mixed characters
    if (type === 'password' || type === 'email' || type === 'hidden' || type === 'file') return;
    if (name.includes('title') || name.includes('description')) return;

    // Strict Letters Only fields
    const isLettersOnly = name.includes('name') || name === 'department';

    // Strict Numbers Only fields
    const isNumbersOnly = type === 'number' || name === 'age' || name.includes('amount') || name.includes('budget') || name.includes('balance');

    if (isLettersOnly) {
      // Remove anything that is NOT a letter, space, period, or hyphen
      if (/[^a-zA-Z\s.-]/.test(e.target.value)) {
        e.target.value = e.target.value.replace(/[^a-zA-Z\s.-]/g, '');
      }
    } else if (isNumbersOnly) {
      // Remove anything that is NOT a digit or a decimal point
      if (/[^0-9.]/.test(e.target.value)) {
        e.target.value = e.target.value.replace(/[^0-9.]/g, '');
      }
    }
  });
});

// ============================================================
// Chart helpers
// ============================================================

const SSC_COLORS = {
  navy: '#0D2B5C',
  gold: '#F5A623',
  teal: '#00A878',
  danger: '#E63946',
  purple: '#7c3aed',
  orange: '#f4a261',
  blue: '#4299e1',
  green: '#48bb78',
};

const PALETTE = [
  SSC_COLORS.navy, SSC_COLORS.gold, SSC_COLORS.teal, SSC_COLORS.danger,
  SSC_COLORS.purple, SSC_COLORS.orange, SSC_COLORS.blue, SSC_COLORS.green
];

function generateChartColors(count) {
  if (count <= PALETTE.length) {
    return PALETTE.slice(0, count);
  }

  const colors = [...PALETTE];
  for (let i = PALETTE.length; i < count; i++) {
    const hue = Math.round((i / count) * 360);
    colors.push(`hsl(${hue}, 72%, 55%)`);
  }
  return colors;
}

function createPieChart(canvasId, labels, data) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;

  const total = data.reduce((a, b) => a + b, 0);
  const colors = generateChartColors(data.length);

  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: colors,
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 12,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      layout: { padding: 10 },
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 12,
            font: { family: 'Inter', size: 12 },
            boxWidth: 12,
            generateLabels: chart => {
              const items = Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart);
              return items.map((item, index) => {
                const value = data[index] || 0;
                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                return {
                  ...item,
                  text: `${item.text} (${percent}%)`,
                };
              });
            }
          }
        },
        tooltip: {
          callbacks: {
            title: ctx => ctx[0].label,
            label: ctx => {
              const value = ctx.parsed;
              const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
              return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2 })} (${percent}%)`;
            }
          }
        }
      }
    },
    plugins: [{
      id: 'centerTextPlugin',
      beforeDraw: function(chart) {
        const ctx = chart.ctx;
        const meta = chart.getDatasetMeta(0);
        if (!meta.data.length) return;
        const x = meta.data[0].x;
        const y = meta.data[0].y;

        ctx.restore();

        ctx.font = 'bold 1.15rem Inter, sans-serif';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#0f172a';
        const text = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const textWidth = ctx.measureText(text).width;
        ctx.fillText(text, x - (textWidth / 2), y - 6);

        ctx.font = '600 0.75rem Inter, sans-serif';
        ctx.fillStyle = '#64748b';
        const subText = 'TOTAL BUDGET';
        const subTextWidth = ctx.measureText(subText).width;
        ctx.fillText(subText, x - (subTextWidth / 2), y + 14);

        ctx.save();
      }
    }]
  });
}

function createBarChart(canvasId, labels, data, label = 'Amount (₱)') {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label,
        data,
        backgroundColor: 'rgba(13,43,92,0.8)',
        borderRadius: 6,
        hoverBackgroundColor: SSC_COLORS.gold,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })
          }
        }
      },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f0f4f8' }, ticks: { callback: v => '₱' + v.toLocaleString() } },
        x: { grid: { display: false } }
      }
    }
  });
}

function createLineChart(canvasId, labels, data, label = 'Budget Trend') {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  return new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label,
        data,
        borderColor: SSC_COLORS.teal,
        backgroundColor: 'rgba(0,168,120,0.08)',
        tension: 0.4, fill: true,
        pointBackgroundColor: SSC_COLORS.teal,
        pointRadius: 5, pointHoverRadius: 8,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })
          }
        }
      },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f0f4f8' }, ticks: { callback: v => '₱' + v.toLocaleString() } },
        x: { grid: { display: false } }
      }
    }
  });
}

