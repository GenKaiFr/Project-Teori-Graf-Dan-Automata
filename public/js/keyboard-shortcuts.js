class KeyboardShortcuts {
    constructor() {
        this.shortcuts = {
            'ctrl+shift+d': () => this.toggleDarkMode(),
            'ctrl+shift+h': () => this.goHome(),
            'ctrl+shift+m': () => this.goToMeetings(),
            'ctrl+shift+c': () => this.goToCalendar(),
            'ctrl+n': () => this.createNewMeeting(),
            'ctrl+s': (e) => this.saveForm(e),
            'esc': () => this.closeModal(),
            'ctrl+shift+/': () => this.showShortcutsHelp()
        };
        this.init();
    }
    
    init() {
        document.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.createShortcutsModal();
    }
    
    handleKeydown(e) {
        const key = this.getKeyString(e);
        if (this.shortcuts[key]) {
            if (key !== 'ctrl+s' || !this.isInForm(e.target)) {
                e.preventDefault();
            }
            this.shortcuts[key](e);
        }
    }
    
    getKeyString(e) {
        const parts = [];
        if (e.ctrlKey) parts.push('ctrl');
        if (e.shiftKey) parts.push('shift');
        const key = e.key.toLowerCase();
        if (key === 'escape') parts.push('esc');
        else if (key !== 'control' && key !== 'shift') parts.push(key);
        return parts.join('+');
    }
    
    isInForm(element) {
        return element.closest('form') !== null;
    }
    
    toggleDarkMode() {
        const toggle = document.getElementById('darkModeToggle');
        if (toggle) toggle.click();
    }
    
    goHome() { window.location.href = '/'; }
    goToMeetings() { window.location.href = '/meetings'; }
    goToCalendar() { window.location.href = '/calendar'; }
    
    createNewMeeting() {
        if (document.querySelector('a[href*="/meetings/create"]')) {
            window.location.href = '/meetings/create';
        }
    }
    
    saveForm(e) {
        const form = e.target.closest('form');
        if (form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) submitBtn.click();
        }
    }
    
    closeModal() {
        const modals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
        modals.forEach(modal => {
            const closeBtn = modal.querySelector('button[onclick*="close"]');
            if (closeBtn) closeBtn.click();
        });
    }
    
    showShortcutsHelp() {
        document.getElementById('shortcutsModal').classList.remove('hidden');
    }
    
    createShortcutsModal() {
        const modal = document.createElement('div');
        modal.id = 'shortcutsModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden z-50';
        modal.innerHTML = `
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Keyboard Shortcuts</h3>
                            <button onclick="document.getElementById('shortcutsModal').classList.add('hidden')" 
                                    class="text-gray-500 dark:text-gray-400">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Dark Mode</span>
                                <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Ctrl+Shift+D</kbd>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Home</span>
                                <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Ctrl+Shift+H</kbd>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Meetings</span>
                                <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Ctrl+Shift+M</kbd>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Calendar</span>
                                <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Ctrl+Shift+C</kbd>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">New Meeting</span>
                                <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Ctrl+N</kbd>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Save Form</span>
                                <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Ctrl+S</kbd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
}

document.addEventListener('DOMContentLoaded', () => new KeyboardShortcuts());