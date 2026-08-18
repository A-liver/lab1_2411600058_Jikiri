document.addEventListener("DOMContentLoaded", function () {

    const isLoggedin = localStorage.getItem('isLoggedIn');
    if (isLoggedin !== 'true') {
        window.location.href = 'index.html';
        return;
    }
    const username = localStorage.getItem('user') || 'User';
    updateGreeting(username);

    updateStatistics();
    populateActivityTable();
    setupLogout();

    const userNamespan = document.getElementById('userName');
    if (userNamespan) {
        userNamespan.textContent = username;
    }
});

function updateGreeting(username) {
    const greetingElement = document.getElementById('greeting');
    if (!greetingElement) return;

    const hour = new Date().getHours();
    let timeOfDay = '';

    if (hour >= 5 && hour < 12) {
        timeOfDay = 'Good Morning';
    } else if (hour >= 12 && hour < 17) {
        timeOfDay = 'Good Afternoon';
    } else if (hour >= 17 && hour < 21) {
        timeOfDay = 'Good Evening';
    } else {
        timeOfDay = 'Good Night';
    }

    greetingElement.textContent = `${timeOfDay}, ${username}!`;
}

function updateStatistics() {
    const stats = [
        { title: "Today's Orders", value: '32', color: 'text-primary', icon: '🍽' },
        { title: "Today's Revenue", value: '$19,150', color: 'text-success', icon: '💰' },
        { title: 'Reservation', value: '12', color: 'text-info', icon: '📅' },
        { title: 'Available Tables', value: '8', color: 'text-warning', icon: '🪑' }
    ];

    const cardTitles = document.querySelectorAll('[id^="stat"][id$="-title"]');
    const cardValues = document.querySelectorAll('[id^="stat"][id$="-value"]');

    stats.forEach((stat, index) => {
        const titleElement = document.getElementById(`stat${index + 1}-title`);
        const valueElement = document.getElementById(`stat${index + 1}-value`);

        if (titleElement) {
            titleElement.textContent = `${stat.icon} ${stat.title}`;
        }
        if (valueElement) {
            valueElement.textContent = stat.value;
            // Remove existing color classes and add the new one
            valueElement.className = `card-text fw-bold ${stat.color}`;
        }
    });
}

function populateActivityTable() {
    const tableBody = document.getElementById('activityTableBody');
    if (!tableBody) return;

    const activities = [
        { date: '2026-08-10 14:30', activity: 'New reservation received for Table 8', status: 'success' },
        { date: '2026-08-10 13:15', activity: 'Menu item "Beef Steak" updated', status: 'info' },
        { date: '2026-08-10 11:45', activity: 'Low inventory alert: Chicken Breast', status: 'warning' },
        { date: '2026-08-10 09:00', activity: 'New Customer Reservation Up', status: 'success' },
        { date: '2026-08-09 16:20', activity: 'Inventory restocked: Soft Drinks', status: 'success' },
        { date: '2026-08-09 14:10', activity: 'Table 5 reservation has been cancelled', status: 'danger' }

    ];

    tableBody.innerHTML = '';

    activities.forEach(activity => {
        const row = document.createElement('tr');

        let badgeClass = 'bg-secondary';
        if (activity.status === 'success') badgeClass = 'bg-success';
        else if (activity.status === 'warning') badgeClass = 'bg-warning text-dark';
        else if (activity.status === 'danger') badgeClass = 'bg-danger';
        else if (activity.status === 'info') badgeClass = 'bg-info text-dark';

        row.innerHTML = `
            <td>${activity.date}</td>
            <td>${activity.activity}</td>
            <td><span class ="badge ${badgeClass}">${activity.status}</span></td>
        `;

        tableBody.appendChild(row);
    });
}

function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutLink = document.getElementById('logoutLink');

    function performLogout(e) {
        e.preventDefault();

        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', performLogout);
    }
    if (logoutLink) {
        logoutLink.addEventListener('click', performLogout);
    }
}