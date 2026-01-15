const API_BASE = '/api';

const app = {
    currentView: 'today',
    currentZone: 8741,
    zones: [],
    
    init() {
        this.setupTabs();
        this.loadZones();
    },
    
    async loadZones() {
        try {
            const data = await this.fetchAPI('/zones');
            this.zones = data.data.zones;
            this.renderZoneSelector();
            this.loadView('today');
        } catch(error) {
            console.error('Error loading zones:', error);
            this.loadView('today');
        }
    },
    
    renderZoneSelector() {
        const header = document.querySelector('header');
        
        let html = '<div class="zone-selector">';
        html += '<label for="zone-select">Zona geografica:</label>';
        html += '<select id="zone-select">';
        
        this.zones.forEach(zone => {
            const selected = zone.geo_id === this.currentZone ? 'selected' : '';
            html += '<option value="' + zone.geo_id + '" ' + selected + '>' + zone.geo_name + '</option>';
        });
        
        html += '</select>';
        html += '</div>';
        
        header.insertAdjacentHTML('beforeend', html);
        
        document.getElementById('zone-select').addEventListener('change', (e) => {
            this.currentZone = parseInt(e.target.value);
            this.loadView(this.currentView);
        });
    },
    
    setupTabs() {
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                e.target.classList.add('active');
                const view = e.target.dataset.view;
                this.loadView(view);
            });
        });
    },
    
    async loadView(view) {
        this.currentView = view;
        const content = document.getElementById('content');
        content.innerHTML = '<div class="loading">Cargando...</div>';
        
        try {
            switch(view) {
                case 'today':
                    await this.renderToday();
                    break;
                case 'tomorrow':
                    await this.renderTomorrow();
                    break;
                case 'tasks':
                    await this.renderTasks();
                    break;
                case 'hours':
                    await this.renderHours();
                    break;
            }
        } catch(error) {
            if (error.message.includes('No data available')) {
                content.innerHTML = '<div class="info-message"><h3>Datos no disponibles</h3><p>Los datos para esta fecha y zona aun no estan disponibles. Por favor, intenta mas tarde o selecciona otra zona.</p></div>';
            } else {
                content.innerHTML = '<div class="error">Error al cargar datos: ' + error.message + '</div>';
            }
        }
    },
    
    async renderToday() {
        const data = await this.fetchAPI('/today?geo_id=' + this.currentZone);
        this.renderSummary(data.data);
    },
    
    async renderTomorrow() {
        const data = await this.fetchAPI('/tomorrow?geo_id=' + this.currentZone);
        this.renderSummary(data.data);
    },
    
    renderSummary(data) {
        const content = document.getElementById('content');
        
        let html = '<h2>Resumen del ' + data.date + ' - ' + data.geo_name + '</h2>';
        
        html += '<div class="price-range">';
        html += '<div class="price-stat"><div class="label">Precio Minimo</div><div class="value">' + data.price_range.min + ' EUR/kWh</div></div>';
        html += '<div class="price-stat"><div class="label">Precio Medio</div><div class="value">' + data.price_range.avg + ' EUR/kWh</div></div>';
        html += '<div class="price-stat"><div class="label">Precio Maximo</div><div class="value">' + data.price_range.max + ' EUR/kWh</div></div>';
        html += '</div>';
        
        html += '<h3>Recomendaciones por Tarea</h3>';
        html += '<div class="recommendations">';
        
        data.recommendations.forEach(rec => {
            html += '<div class="recommendation-card">';
            html += '<h3>' + rec.task + '</h3>';
            html += '<div class="hours">';
            rec.recommended_hours.forEach(hour => {
                html += '<span class="hour-badge">' + String(hour).padStart(2, '0') + ':00</span>';
            });
            html += '</div>';
            html += '<div class="message">' + rec.message + '</div>';
            html += '</div>';
        });
        
        html += '</div>';
        
        content.innerHTML = html;
    },
    
    async renderHours() {
        const data = await this.fetchAPI('/hours?geo_id=' + this.currentZone);
        const content = document.getElementById('content');
        
        let html = '<h2>Precios por Hora - ' + data.data.date + ' - ' + data.data.geo_name + '</h2>';
        html += '<div class="hours-grid">';
        
        data.data.hours.forEach(hour => {
            html += '<div class="hour-card ' + hour.classification + '">';
            html += '<div class="time">' + String(hour.hour).padStart(2, '0') + ':00</div>';
            html += '<div class="price">' + hour.price + ' EUR/kWh</div>';
            html += '<div class="label">' + hour.label + '</div>';
            html += '</div>';
        });
        
        html += '</div>';
        
        content.innerHTML = html;
    },
    
    async renderTasks() {
        const content = document.getElementById('content');
        const tasks = ['lavadora', 'secadora', 'horno', 'lavavajillas'];
        
        let html = '<h2>Recomendaciones por Tarea</h2>';
        html += '<div class="task-selector">';
        
        tasks.forEach((task, index) => {
            const active = index === 0 ? 'active' : '';
            const label = task.charAt(0).toUpperCase() + task.slice(1);
            html += '<button class="task-btn ' + active + '" data-task="' + task + '">' + label + '</button>';
        });
        
        html += '</div>';
        html += '<div id="task-detail"></div>';
        
        content.innerHTML = html;
        
        document.querySelectorAll('.task-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                document.querySelectorAll('.task-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                await this.loadTaskDetail(e.target.dataset.task);
            });
        });
        
        await this.loadTaskDetail('lavadora');
    },
    
    async loadTaskDetail(taskCode) {
        try {
            const data = await this.fetchAPI('/task/' + taskCode + '?geo_id=' + this.currentZone);
            const detail = document.getElementById('task-detail');
            
            let html = '<div class="task-detail">';
            html += '<h2>' + data.data.task + '</h2>';
            html += '<div class="message">' + data.data.message + '</div>';
            html += '<h3>Horas Recomendadas</h3>';
            html += '<div class="hours">';
            
            data.data.recommended_hours.forEach(hour => {
                html += '<span class="hour-badge">' + String(hour).padStart(2, '0') + ':00</span>';
            });
            
            html += '</div>';
            html += '</div>';
            
            detail.innerHTML = html;
        } catch(error) {
            const detail = document.getElementById('task-detail');
            detail.innerHTML = '<div class="info-message"><p>Datos no disponibles para esta tarea en la zona seleccionada.</p></div>';
        }
    },
    
    async fetchAPI(endpoint) {
        const response = await fetch(API_BASE + endpoint);
        const data = await response.json();
        
        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Error al obtener datos');
        }
        
        return data;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    app.init();
});
