import './bootstrap';

import Alpine from 'alpinejs';

const DIAS_CORTOS = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

function aISO(d) {
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const dia = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${m}-${dia}`;
}

/**
 * Selector de día y horario libre. Consulta la disponibilidad real del profesional
 * cada vez que cambia el día o el servicio.
 */
window.selectorHorario = ({ url, servicio, fecha = null, hora = null, ignorar = null, dias = [], max = 60 }) => ({
    servicio: String(servicio ?? ''),
    fecha,
    hora,
    horarios: [],
    estado: 'inicial', // inicial | cargando | listo | vacio | error
    desde: 0,
    porPagina: 14,
    dias: [],

    init() {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        for (let i = 0; i <= max; i++) {
            const d = new Date(hoy);
            d.setDate(hoy.getDate() + i);
            const iso = d.getDay() === 0 ? 7 : d.getDay();
            this.dias.push({
                iso: aISO(d),
                corto: DIAS_CORTOS[d.getDay()],
                numero: d.getDate(),
                mes: MESES[d.getMonth()],
                atiende: dias.includes(iso),
                hoy: i === 0,
            });
        }

        if (!this.fecha || !this.dias.some((d) => d.iso === this.fecha && d.atiende)) {
            this.fecha = this.dias.find((d) => d.atiende)?.iso ?? null;
        }
        const idx = this.dias.findIndex((d) => d.iso === this.fecha);
        if (idx >= this.porPagina) this.desde = Math.floor(idx / this.porPagina) * this.porPagina;

        this.$watch('servicio', () => { this.hora = null; this.cargar(); });
        this.cargar(hora);
    },

    get visibles() {
        return this.dias.slice(this.desde, this.desde + this.porPagina);
    },

    get rotuloMes() {
        const v = this.visibles;
        if (!v.length) return '';
        const a = v[0].mes, b = v[v.length - 1].mes;
        return a === b ? a : `${a} – ${b}`;
    },

    mover(paso) {
        const nuevo = this.desde + paso * this.porPagina;
        if (nuevo < 0 || nuevo >= this.dias.length) return;
        this.desde = nuevo;
    },

    elegirDia(d) {
        if (!d.atiende) return;
        this.fecha = d.iso;
        this.hora = null;
        this.cargar();
    },

    async cargar(conservar = null) {
        if (!this.fecha || !this.servicio) {
            this.estado = 'vacio';
            this.horarios = [];
            return;
        }
        this.estado = 'cargando';
        try {
            const params = new URLSearchParams({ servicio: this.servicio, fecha: this.fecha });
            if (ignorar) params.set('ignorar', ignorar);
            const res = await fetch(`${url}?${params}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(res.status);
            const data = await res.json();
            this.horarios = data.horarios;
            this.estado = this.horarios.length ? 'listo' : 'vacio';
            if (conservar && this.horarios.some((h) => h.inicio === conservar)) this.hora = conservar;
        } catch (e) {
            this.estado = 'error';
            this.horarios = [];
        }
    },

    get fechaLegible() {
        if (!this.fecha) return '';
        const [y, m, d] = this.fecha.split('-').map(Number);
        const f = new Date(y, m - 1, d);
        return f.toLocaleDateString('es-BO', { weekday: 'long', day: 'numeric', month: 'long' });
    },
});

window.Alpine = Alpine;

Alpine.start();
