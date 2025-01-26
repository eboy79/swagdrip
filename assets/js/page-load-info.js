import gsap from 'gsap';

document.addEventListener("DOMContentLoaded", function () {
    const pageLoadInfo = document.getElementById("page-load-info");
    const fileSizeValue = document.getElementById("file-size-value");
    const domLoadTimeValue = document.getElementById("dom-load-time-value");
    const pageLoadTimeValue = document.getElementById("page-load-time-value");
    const eventsList = document.getElementById("events-list");
    const toggleStats = document.getElementById("toggle-stats");

    const updateLoadTimes = () => {
        const performanceTiming = window.performance.timing;
        const pageLoadTime = performanceTiming.loadEventEnd > 0 ? performanceTiming.loadEventEnd - performanceTiming.navigationStart : "N/A";
        const domContentLoadedTime = performanceTiming.domContentLoadedEventEnd > 0 ? performanceTiming.domContentLoadedEventEnd - performanceTiming.navigationStart : "N/A";

        const calculateTotalFileSize = () => {
            const resourceEntries = performance.getEntriesByType('resource');
            let totalSize = 0;
            resourceEntries.forEach(entry => {
                if (entry.transferSize && entry.transferSize > 0) {
                    totalSize += entry.transferSize;
                } else if (entry.encodedBodySize && entry.encodedBodySize > 0) {
                    totalSize += entry.encodedBodySize;
                }
            });
            return totalSize;
        };

        const formatBytes = (bytes, decimals = 2) => {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        };

        const totalFileSize = calculateTotalFileSize();

        fileSizeValue.innerText = `${formatBytes(totalFileSize)}`;
        domLoadTimeValue.innerText = typeof domContentLoadedTime === 'number' ? `${domContentLoadedTime.toFixed(2)} ms` : domContentLoadedTime;
        pageLoadTimeValue.innerText = typeof pageLoadTime === 'number' ? `${pageLoadTime.toFixed(2)} ms` : pageLoadTime;

        console.log('Total File Size:', fileSizeValue.innerText);
        console.log('DOM Content Loaded Time:', domLoadTimeValue.innerText);
        console.log('Page Load Time:', pageLoadTimeValue.innerText);

        const logResources = () => {
            const resources = performance.getEntriesByType('resource');
            resources.forEach(resource => {
                const listItem = document.createElement('li');
                listItem.textContent = `${resource.name} - ${resource.responseEnd.toFixed(2)} ms - ${formatBytes(resource.encodedBodySize || resource.transferSize)}`;
                eventsList.appendChild(listItem);
            });
        };
        logResources();
    };

    if (pageLoadInfo && fileSizeValue && domLoadTimeValue && pageLoadTimeValue && eventsList && toggleStats) {
        pageLoadInfo.style.display = 'block';

        const logEvent = (event) => {
            const listItem = document.createElement('li');
            listItem.textContent = `${event.type} at ${performance.now().toFixed(2)} ms`;
            eventsList.appendChild(listItem);
        };

        ['DOMContentLoaded', 'load', 'beforeunload', 'unload'].forEach(eventType => {
            window.addEventListener(eventType, logEvent);
        });

        window.addEventListener('load', updateLoadTimes);

        toggleStats.addEventListener('click', () => {
            if (pageLoadInfo.classList.contains('collapsed')) {
                gsap.to("#page-load-info", { x: 0, opacity: 0.93, duration: 0.5 });
                gsap.to("#page-load-info *", { opacity: 1, duration: 0.5 });
                toggleStats.innerHTML = '<svg width="24" height="24"><line x1="5" y1="5" x2="19" y2="19" style="stroke:white;stroke-width:2" /><line x1="5" y1="19" x2="19" y2="5" style="stroke:white;stroke-width:2" /></svg>';
            } else {
                gsap.to("#page-load-info", { x: "100%", opacity: 0, duration: 0.5 });
                gsap.to("#page-load-info *", { opacity: 0, duration: 0.5 });
                toggleStats.innerHTML = '<svg width="24" height="24"><line x1="5" y1="12" x2="19" y2="12" style="stroke:white;stroke-width:2" /></svg>';
            }
            pageLoadInfo.classList.toggle('collapsed');
        });

        gsap.to("#page-load-info", { x: "100%", opacity: 0, duration: 0.5 });
        gsap.to("#page-load-info *", { opacity: 0, duration: 0.5 });
        toggleStats.innerHTML = '<svg width="24" height="24"><line x1="5" y1="12" x2="19" y2="12" style="stroke:white;stroke-width:2" /></svg>';
        pageLoadInfo.classList.add('collapsed');
    } else {
        console.error('One or more elements not found:', { pageLoadInfo, fileSizeValue, domLoadTimeValue, pageLoadTimeValue, eventsList, toggleStats });
    }
});
