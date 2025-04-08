// visitor-counter.js - Enhanced Version
class VisitorCounter {
  constructor(options = {}) {
    // Configuration with defaults
    this.config = {
      counterElementId: 'visitor-count',
      storageKey: 'fayda_saccos_visitor_',
      apiEndpoint: null,
      showUniqueVisitors: true,
      showLiveCounter: true,
      animationDuration: 2000,
      debug: false,
      ...options
    };

    // Elements
    this.counterElement = document.getElementById(this.config.counterElementId);
    if (!this.counterElement) {
      if (this.config.debug) console.warn('Visitor counter element not found');
      return;
    }

    // Initialize
    this.init();
  }

  async init() {
    try {
      // Show loading state
      this.counterElement.textContent = '...';

      if (this.config.apiEndpoint) {
        await this.updateServerCount();
      } else {
        this.updateLocalCount();
      }

      if (this.config.showLiveCounter) {
        this.animateCounter();
      }
    } catch (error) {
      this.handleError(error);
    }
  }

  updateLocalCount() {
    // Get current date
    const today = new Date().toDateString();
    const dateKey = this.config.storageKey + 'date';

    // Get or initialize counts
    let totalCount = localStorage.getItem(this.config.storageKey + 'total') || 1000;
    let uniqueCount = localStorage.getItem(this.config.storageKey + 'unique') || 800;
    let lastDate = localStorage.getItem(dateKey);

    // Convert to numbers
    totalCount = parseInt(totalCount);
    uniqueCount = parseInt(uniqueCount);

    // Check if this is a new day
    if (lastDate !== today) {
      localStorage.setItem(dateKey, today);
      
      // Daily count (optional)
      const dailyKey = this.config.storageKey + 'daily_' + today;
      let dailyCount = localStorage.getItem(dailyKey) || 0;
      dailyCount = parseInt(dailyCount) + 1;
      localStorage.setItem(dailyKey, dailyCount);
    }

    // Check if this is a new session
    if (!sessionStorage.getItem(this.config.storageKey + 'session')) {
      uniqueCount++;
      sessionStorage.setItem(this.config.storageKey + 'session', 'true');
    }

    // Always increment total count
    totalCount++;

    // Save updated counts
    localStorage.setItem(this.config.storageKey + 'total', totalCount);
    localStorage.setItem(this.config.storageKey + 'unique', uniqueCount);

    // Display the count
    const displayCount = this.config.showUniqueVisitors ? uniqueCount : totalCount;
    this.counterElement.textContent = this.formatNumber(displayCount);
    this.counterElement.setAttribute('title', `${totalCount} total views`);
    this.counterElement.setAttribute('data-total', totalCount);
    this.counterElement.setAttribute('data-unique', uniqueCount);

    if (this.config.debug) {
      console.log('Visitor counts updated:', {
        total: totalCount,
        unique: uniqueCount,
        element: this.counterElement
      });
    }
  }

  async updateServerCount() {
    try {
      const response = await fetch(this.config.apiEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          referrer: document.referrer,
          path: window.location.pathname
        })
      });

      if (!response.ok) throw new Error('Network response was not ok');

      const data = await response.json();
      
      this.counterElement.textContent = this.formatNumber(data.count);
      this.counterElement.setAttribute('data-total', data.totalCount || data.count);
      this.counterElement.setAttribute('data-unique', data.uniqueCount || data.count);

      if (this.config.debug) {
        console.log('Server count received:', data);
      }
    } catch (error) {
      // Fallback to local counting if server fails
      if (this.config.debug) console.error('Server count failed, falling back to local:', error);
      this.updateLocalCount();
    }
  }

  animateCounter() {
    const target = parseInt(this.counterElement.textContent.replace(/,/g, ''));
    const start = Math.max(0, target - 100);
    const duration = this.config.animationDuration;
    const startTime = performance.now();

    const animate = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const value = Math.floor(progress * (target - start) + start);
      
      this.counterElement.textContent = this.formatNumber(value);
      
      if (progress < 1) {
        requestAnimationFrame(animate);
      } else {
        this.counterElement.textContent = this.formatNumber(target);
      }
    };

    this.counterElement.textContent = this.formatNumber(start);
    requestAnimationFrame(animate);
  }

  formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '+';
  }

  handleError(error) {
    console.error('Visitor counter error:', error);
    this.counterElement.textContent = '1,000+'; // Fallback value
    this.counterElement.style.color = '#ccc';
  }

  // Static method for quick initialization
  static autoInit() {
    document.addEventListener('DOMContentLoaded', () => {
      new VisitorCounter();
    });
  }
}

// Initialize automatically if script is loaded directly
if (typeof module === 'undefined') {
  // Browser environment - auto initialize
  VisitorCounter.autoInit();
} else {
  // Module environment - export class
  module.exports = VisitorCounter;
}