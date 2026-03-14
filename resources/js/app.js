import './bootstrap';
import Chart from 'chart.js/auto';
import flatpickr from 'flatpickr';
import { Portuguese } from 'flatpickr/dist/l10n/pt.js';
import 'flatpickr/dist/flatpickr.min.css';
import '@fontsource/manrope/400.css';
import '@fontsource/manrope/500.css';
import '@fontsource/manrope/600.css';
import '@fontsource/manrope/700.css';
import '@fontsource/sora/400.css';
import '@fontsource/sora/600.css';
import '@fontsource/sora/700.css';
import '@fontsource/sora/800.css';
import '@fontsource-variable/instrument-sans';
import 'material-design-icons-iconfont/dist/material-design-icons.css';

window.Chart = Chart;
window.flatpickr = flatpickr;
window.flatpickr.l10ns.pt = Portuguese;
window.financeMonthChart = null;
window.financeValuesVisible = true;

const areFinanceValuesVisible = () => window.financeValuesVisible !== false;

const categoryValueLabelsPlugin = {
	id: 'categoryValueLabels',
	afterDatasetsDraw(chart) {
		if (!areFinanceValuesVisible()) {
			return;
		}

		const datasetMeta = chart.getDatasetMeta(0);
		const dataset = chart.data.datasets[0];
		const ctx = chart.ctx;

		if (!datasetMeta || !dataset || !Array.isArray(dataset.data)) {
			return;
		}

		ctx.save();
		ctx.font = '700 11px Manrope';
		ctx.fillStyle = '#334155';
		ctx.textBaseline = 'middle';

		datasetMeta.data.forEach((barElement, index) => {
			const valor = Number(dataset.data[index] || 0);

			if (valor <= 0) {
				return;
			}

			const label = `R$ ${valor.toLocaleString('pt-BR', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2,
			})}`;

			ctx.fillText(label, barElement.x + 8, barElement.y);
		});

		ctx.restore();
	},
};

window.initFinanceMonthChart = (root = document) => {
	const canvas = root.querySelector('#finance-month-chart');

	if (!canvas) {
		return;
	}

	if (window.financeMonthChart) {
		window.financeMonthChart.destroy();
	}

	let despesasPorCategoria = {};

	try {
		despesasPorCategoria = JSON.parse(canvas.dataset.despesasPorCategoria || '{}');
	} catch (error) {
		despesasPorCategoria = {};
	}

	const categoriasOrdenadas = Object.entries(despesasPorCategoria)
		.map(([categoria, valor]) => [categoria, Number(valor || 0)])
		.sort((a, b) => {
			if (b[1] !== a[1]) {
				return b[1] - a[1];
			}

			return String(a[0]).localeCompare(String(b[0]), 'pt-BR');
		});

	const labels = categoriasOrdenadas.length > 0 ? categoriasOrdenadas.map(([categoria]) => categoria) : ['Sem despesas no periodo'];
	const data = categoriasOrdenadas.length > 0 ? categoriasOrdenadas.map(([, valor]) => valor) : [0];
	const mesAno = canvas.dataset.periodo || 'Periodo';

	const palette = ['#e86820', '#1e4a66', '#208070', '#c05010', '#0ea5e9', '#b45309', '#15803d', '#a21caf', '#2563eb', '#9333ea'];
	const barColors = labels.map((_, index) => palette[index % palette.length]);

	window.financeMonthChart = new Chart(canvas, {
		type: 'bar',
		plugins: [categoryValueLabelsPlugin],
		data: {
			labels,
			datasets: [
				{
					label: 'Despesas por categoria',
					data,
					backgroundColor: barColors,
					borderRadius: 10,
					maxBarThickness: 34,
				},
			],
		},
		options: {
			maintainAspectRatio: false,
			indexAxis: 'y',
			scales: {
				x: {
					grid: {
						color: 'rgba(148, 163, 184, 0.25)',
					},
					ticks: {
						callback(value) {
							if (!areFinanceValuesVisible()) {
								return 'R$ •••••';
							}

							return `R$ ${Number(value).toLocaleString('pt-BR')}`;
						},
						font: {
							family: 'Manrope',
							size: 11,
						},
					},
				},
				y: {
					grid: {
						display: false,
					},
					ticks: {
						font: {
							family: 'Manrope',
							size: 12,
						},
					},
				},
			},
			plugins: {
				legend: {
					display: false,
				},
				title: {
					display: true,
					text: `Gastos por categoria em ${mesAno}`,
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
							const valor = Number(
								(context.parsed && typeof context.parsed === 'object' ? (context.parsed.x ?? context.parsed.y) : context.parsed)
								?? context.raw
								?? 0,
							);
							const categoria = context.label || 'Categoria';

							if (!areFinanceValuesVisible()) {
								return `${categoria}: R$ •••••`;
							}

							return `${categoria}: R$ ${valor.toLocaleString('pt-BR', {
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

window.setFinanceChartValuesVisibility = (isVisible) => {
	window.financeValuesVisible = Boolean(isVisible);

	if (window.financeMonthChart) {
		window.financeMonthChart.update();
	}
};

document.addEventListener('DOMContentLoaded', () => {
	window.initFinanceMonthChart(document);
});
