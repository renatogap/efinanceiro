import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;
window.financeMonthChart = null;

window.initFinanceMonthChart = (root = document) => {
	const canvas = root.querySelector('#finance-month-chart');

	if (!canvas) {
		return;
	}

	if (window.financeMonthChart) {
		window.financeMonthChart.destroy();
	}

	const receitas = Number(canvas.dataset.receitas || 0);
	const despesas = Number(canvas.dataset.despesas || 0);
	const mesAno = canvas.dataset.periodo || 'Periodo';

	window.financeMonthChart = new Chart(canvas, {
		type: 'doughnut',
		data: {
			labels: ['Receitas', 'Despesas'],
			datasets: [
				{
					data: [receitas, despesas],
					backgroundColor: ['#16a34a', '#dc2626'],
					borderWidth: 0,
				},
			],
		},
		options: {
			maintainAspectRatio: false,
			cutout: '62%',
			plugins: {
				legend: {
					position: 'bottom',
					labels: {
						usePointStyle: true,
						boxWidth: 10,
						font: {
							family: 'Manrope',
							size: 12,
						},
					},
				},
				title: {
					display: true,
					text: `Distribuicao em ${mesAno}`,
					font: {
						family: 'Sora',
						weight: '700',
						size: 14,
					},
					color: '#0f172a',
					padding: {
						bottom: 16,
					},
				},
				tooltip: {
					callbacks: {
						label(context) {
							const valor = Number(context.parsed || 0);

							return `${context.label}: R$ ${valor.toLocaleString('pt-BR', {
								minimumFractionDigits: 2,
								maximumFractionDigits: 2,
							})}`;
						},
					},
				},
			},
		},
	});
};

document.addEventListener('DOMContentLoaded', () => {
	window.initFinanceMonthChart(document);
});
