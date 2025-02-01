import gsap from 'gsap';

class TerminalMonitor {
    constructor() {
        this.state = {
            startTime: performance.now(),
            resources: new Map(),
            webVitals: {
                lcp: null,
                fid: null,
                cls: 0
            }
        };
        
        this.elements = {
            container: document.getElementById('page-load-info'),
            fileSize: document.getElementById('file-size-value'),
            domLoadTime: document.getElementById('dom-load-time-value'),
            pageLoadTime: document.getElementById('page-load-time-value'),
            eventsList: document.getElementById('events-list'),
            toggleStats: document.getElementById('toggle-stats'),  // Added comma here
            closeStats: document.getElementById('close-stats')
        };

        this.init();
    }

    init() {
        this.createWebVitalsSection();
        this.setupObservers();
        this.initializeEventListeners();
        this.setupBackupTimers();
    }

    createWebVitalsSection() {
        // Insert Core Web Vitals after basic stats
        const terminalSection = document.querySelector('.terminal');
        const loadStats = document.getElementById('load-stats');
        
        if (terminalSection && loadStats) {
            const vitalsSection = document.createElement('div');
            vitalsSection.className = 'web-vitals-section';
            vitalsSection.innerHTML = `
                <div class="web-vitals-header">Core Web Vitals</div>
                <div class="web-vitals-stats">
                    <span class="web-vital">LCP: --</span>
                    <span class="web-vital">FID: --</span>
                    <span class="web-vital">CLS: 0.000</span>
                </div>
            `;
            
            loadStats.parentNode.insertBefore(vitalsSection, loadStats.nextSibling);
        }
    }
    setupBackupTimers() {
        document.addEventListener('DOMContentLoaded', () => {
            const domTime = Math.round(performance.now());
            this.elements.domLoadTime.textContent = `${domTime}ms`;
            this.logEvent('DOMContentLoaded', domTime);
        });
    
        window.addEventListener('load', () => {
            const loadTime = Math.round(performance.now());
            this.elements.pageLoadTime.textContent = `${loadTime}ms`;
            this.logEvent('Page Load Complete', loadTime);
        });
    }
    setupObservers() {
        // Resource Timing
        const resourceObserver = new PerformanceObserver((list) => {
            list.getEntries().forEach(entry => {
                this.processResourceEntry(entry);
            });
        });
        resourceObserver.observe({ entryTypes: ['resource'] });

        // Web Vitals
        const webVitalsObserver = new PerformanceObserver((list) => {
            list.getEntries().forEach(this.processWebVital.bind(this));
        });
        webVitalsObserver.observe({ 
            entryTypes: ['largest-contentful-paint', 'first-input', 'layout-shift']
        });

        // Navigation Timing
        this.updateNavigationTiming();
    }

    processResourceEntry(entry) {
        const type = this.getResourceType(entry.name);
        const size = entry.transferSize || entry.encodedBodySize || 0;
        const fileName = entry.name.split('/').pop();
        
        if (!this.state.resources.has(type)) {
            this.state.resources.set(type, {
                count: 0,
                totalSize: 0,
                totalTime: 0
            });
        }
        
        const resource = this.state.resources.get(type);
        resource.count++;
        resource.totalSize += size;
        resource.totalTime += entry.duration;
        
        // Log the individual resource
        this.logEvent(`Resource loaded: ${type} - ${fileName} - ${this.formatBytes(size)}`, entry.responseEnd);
        
        // Update type summary
        this.logEvent(type, entry.responseEnd, false);
        this.logEvent(`Count: ${resource.count} | Size: ${this.formatBytes(resource.totalSize)} | Time: ${Math.round(resource.totalTime)}ms`, entry.responseEnd, false, true);
        
        // Update total file size
        const totalSize = Array.from(this.state.resources.values())
            .reduce((sum, data) => sum + data.totalSize, 0);
        this.elements.fileSize.textContent = this.formatBytes(totalSize);
    }

    processWebVital(entry) {
        const vitalsStats = document.querySelector('.web-vitals-stats');
        if (!vitalsStats) return;

        switch (entry.entryType) {
            case 'largest-contentful-paint':
                this.state.webVitals.lcp = Math.round(entry.startTime);
                vitalsStats.children[0].textContent = `LCP: ${this.state.webVitals.lcp}ms`;
                break;
            case 'first-input':
                this.state.webVitals.fid = Math.round(entry.processingStart - entry.startTime);
                vitalsStats.children[1].textContent = `FID: ${this.state.webVitals.fid}ms`;
                break;
            case 'layout-shift':
                if (!entry.hadRecentInput) {
                    this.state.webVitals.cls += entry.value;
                    vitalsStats.children[2].textContent = `CLS: ${this.state.webVitals.cls.toFixed(3)}`;
                }
                break;
        }
    }

    logEvent(message, timestamp = performance.now(), withArrow = true, indent = false) {
        const time = Math.round(timestamp);
        const listItem = document.createElement('li');
        listItem.className = 'event-entry';
        
        let html = '';
        if (withArrow) {
            html += '► ';
        }
        html += `<span class="timestamp">${time}ms</span> `;
        if (indent) {
            html += '    ';
        }
        html += message;
        
        listItem.innerHTML = html;
        this.elements.eventsList.appendChild(listItem);
        this.elements.eventsList.scrollTop = this.elements.eventsList.scrollHeight;
    }

    getResourceType(url) {
        if (url.match(/\.(js|jsx)$/)) return 'JS';
        if (url.match(/\.(css|scss)$/)) return 'CSS';
        if (url.match(/\.(jpg|jpeg|png|gif|webp|svg)$/)) return 'IMG';
        if (url.match(/\.(woff|woff2|ttf|otf|eot)$/)) return 'FONT';
        if (url.includes('/wp-json/') || url.includes('admin-ajax.php')) return 'API';
        if (url.includes('/wp-content/plugins/')) return 'PLUGIN';
        return 'OTHER';
    }

    formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
    }

    updateNavigationTiming() {
        // Get initial timing from Navigation API
        const timing = performance.getEntriesByType('navigation')[0];
        
        if (timing) {
            const domContentLoaded = Math.round(timing.domContentLoadedEventEnd - timing.fetchStart);
            const pageLoad = Math.round(timing.loadEventEnd - timing.fetchStart);
            
            // Only update if we have non-zero values
            if (domContentLoaded > 0) {
                this.elements.domLoadTime.textContent = `${domContentLoaded}ms`;
                this.logEvent('DOMContentLoaded', timing.domContentLoadedEventEnd);
            }
            
            if (pageLoad > 0) {
                this.elements.pageLoadTime.textContent = `${pageLoad}ms`;
                this.logEvent('Page Load Complete', timing.loadEventEnd);
            }
        }
    }
    
    initializeEventListeners() {
        if (this.elements.toggleStats) {
            this.elements.toggleStats.addEventListener('click', () => {
                const isCollapsed = this.elements.container.classList.contains('collapsed');
                
                gsap.to("#page-load-info", {
                    x: isCollapsed ? 0 : "100%",
                    opacity: isCollapsed ? 0.93 : 0,
                    duration: 0.5
                });
                
                gsap.to("#page-load-info *", {
                    opacity: isCollapsed ? 1 : 0,
                    duration: 0.5
                });
                
                this.elements.toggleStats.innerHTML = isCollapsed
                    ? '<svg width="24" height="24"><line x1="5" y1="5" x2="19" y2="19" style="stroke:white;stroke-width:2" /><line x1="5" y1="19" x2="19" y2="5" style="stroke:white;stroke-width:2" /></svg>'
                    : '<svg width="24" height="24"><line x1="5" y1="12" x2="19" y2="12" style="stroke:white;stroke-width:2" /></svg>';
                
                this.elements.container.classList.toggle('collapsed');
            });
        }

        // Add close button functionality
        if (this.elements.closeStats) {
            this.elements.closeStats.addEventListener('click', () => {
                // Animate out using same animation as toggle
                gsap.to("#page-load-info", {
                    x: "100%",
                    opacity: 0,
                    duration: 0.5
                });
                
                gsap.to("#page-load-info *", {
                    opacity: 0,
                    duration: 0.5
                });
                
                // Update toggle button to show closed state
                this.elements.toggleStats.innerHTML = '<svg width="24" height="24"><line x1="5" y1="12" x2="19" y2="12" style="stroke:white;stroke-width:2" /></svg>';
                
                // Add collapsed class
                this.elements.container.classList.add('collapsed');
            });
        }
    }
} // End of class

// Add terminal styles
const styles = `
    .web-vitals-section {
        margin: 15px 0;
        font-family: monospace;
    }

    .web-vitals-header {
        color: #00ff00;
        margin-bottom: 5px;
    }

    .web-vitals-stats {
        display: flex;
        gap: 30px;
        color: #fff;
    }

    .web-vital {
        white-space: pre;
    }

    .event-entry {
        font-family: monospace;
        margin: 2px 0;
        white-space: pre;
        color: #fff;
    }

    .timestamp {
        color: #00ff00;
        margin-right: 10px;
        display: inline-block;
    }

    #events-list {
        padding: 0;
        margin: 0;
        list-style: none;
    }

    #events-list li {
        padding-left: 8px;
    }

    .button.red {
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .button.red:hover {
        opacity: 0.8;
    }
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = styles;
document.head.appendChild(styleSheet);

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    window.perfMonitor = new TerminalMonitor();
});