<!-- User Role Meta Tag for Notification System -->
<meta name="user-role" content="cook">

<!-- Cook/Admin Header -->
<header class="cook-header">
    <div class="header-backdrop"></div>
    <div class="header-content">
        <div class="header-left">
            <img src="{{ asset('images/PN-Logo.png') }}"
                 alt="PN Logo" class="header-logo">
        </div>
        <div class="header-right">
            <div class="header-profile">
                <div class="profile-dropdown">
                    <button class="profile-button" onclick="toggleProfileMenu()">
                        <div class="profile-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="profile-info">
                            <span class="profile-name">{{ Auth::user()->name }}</span>
                            <span class="profile-role">Cook Admin</span>
                        </div>
                        <i class="bi bi-chevron-down profile-arrow"></i>
                    </button>
                    <div class="profile-menu" id="profileMenu">
                        <div class="menu-header">
                            <div class="menu-avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div>
                                <p class="menu-name">{{ Auth::user()->name }}</p>
                                <p class="menu-email">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        <div class="menu-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" class="menu-item">
                            @csrf
                            <button type="submit" class="logout-button">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Include notification system for cook -->
@include('Component.notification-dropdown')

<style>
.cook-header {
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1030;
    height: 70px;
    transition: all 0.3s ease;
}

.header-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #22bbea;
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    z-index: -1;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 2rem;
    height: 100%;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 50px;
    width: 50px;
    background: white;
    border-radius: 10px;
    padding: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    margin-left: 10px;
}

.logo-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
}

.header-logo {
    height: 40px;
    width: auto;
    transition: all 0.3s ease;
}

.title-container {
    display: flex;
    flex-direction: column;
}

.header-title {
    color: #22bbea;
    font-weight: 700;
    font-size: 1.1rem;
    margin: 0;
    letter-spacing: 0.5px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.header-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.8rem;
    margin: 0;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.header-right {
    display: flex;
    align-items: center;
}

.profile-dropdown {
    position: relative;
}

.profile-button {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    cursor: pointer;
    border-radius: 10px;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.profile-button:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    border-color: #22bbea;
}

.profile-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(34, 187, 234, 0.1);
    color: #ff9933;
    font-size: 1.2rem;
    border: 1px solid rgba(34, 187, 234, 0.2);
    transition: all 0.3s ease;
}

.profile-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.profile-name {
    font-weight: 600;
    color: white;
    font-size: 0.85rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    line-height: 1.2;
}

.profile-role {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.75rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    line-height: 1.2;
}

.profile-arrow {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
    margin-left: 0.25rem;
}

.profile-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.75rem;
    width: 240px;
    background: rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
    overflow: hidden;
    z-index: 1000;
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.menu-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.menu-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(34, 187, 234, 0.15);
    color: #22bbea;
    font-size: 1.5rem;
    border: 1px solid rgba(34, 187, 234, 0.3);
}

.menu-name {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.3;
}

.menu-email {
    color: #6c757d;
    font-size: 0.75rem;
    margin: 0;
    line-height: 1.3;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.menu-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.05);
    margin: 0.25rem 0;
}

.profile-menu.show {
    display: block;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.25rem;
    color: rgba(255, 255, 255, 0.95);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.menu-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #22bbea;
}

.menu-item::after {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background: transparent;
    transition: all 0.3s ease;
}

.menu-item:hover::after {
    background: #22bbea;
}

.menu-item i {
    margin-right: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    font-size: 1rem;
    width: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.menu-item:hover i {
    color: #22bbea;
    transform: translateX(2px);
}

.menu-item span {
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.menu-item:hover span {
    transform: translateX(2px);
}

.logout-button {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 0.75rem 1.25rem;
    color: rgba(255, 255, 255, 0.95);
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    position: relative;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.logout-button:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ff9933;
}

.logout-button::after {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 3px;
    background: transparent;
    transition: all 0.3s ease;
}

.logout-button:hover::after {
    background: #ff9933;
    box-shadow: 0 0 8px rgba(255, 153, 51, 0.5);
}

.logout-button i {
    margin-right: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    font-size: 1rem;
    width: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.logout-button:hover i {
    color: #dc3545;
    transform: translateX(2px);
}

.logout-button span {
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.logout-button:hover span {
    transform: translateX(2px);
}
</style>

<script>
function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    menu.classList.toggle('show');
}

// Close the menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('profileMenu');
    const button = document.querySelector('.profile-button');
    if (!button.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.remove('show');
    }
});
</script> 