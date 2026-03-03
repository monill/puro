<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">
            <a href="{{ url('/') }}">
                {{ config('app.name', 'Application') }}
            </a>
        </div>
        
        <div class="nav-menu">
            @if(auth)
                @if(can('admin.access'))
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request.segments[1] === 'admin' ? 'active' : '' }}">
                        {{ __('messages.admin') }}
                    </a>
                @endif
                
                @if(can('customer.access'))
                    <a href="{{ route('customer.dashboard') }}" class="nav-link {{ request.segments[1] === 'customer' ? 'active' : '' }}">
                        {{ __('messages.dashboard') }}
                    </a>
                @endif
                
                <a href="{{ route('profile') }}" class="nav-link">
                    {{ __('messages.profile') }}
                </a>
                
                <a href="{{ route('settings') }}" class="nav-link">
                    {{ __('messages.settings') }}
                </a>
            @else
                <a href="{{ route('admin.login') }}" class="nav-link">
                    {{ __('messages.admin_login') }}
                </a>
                
                <a href="{{ route('customer.login') }}" class="nav-link">
                    {{ __('messages.customer_login') }}
                </a>
            @endif
        </div>
        
        @if(auth)
            <div class="nav-user">
                <span class="nav-username">{{ auth.name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="nav-logout-form">
                    {{ csrfField() }}
                    <button type="submit" class="nav-logout-btn">
                        {{ __('messages.logout') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</nav>

<style>
.navbar {
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 60px;
}

.nav-brand a {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    text-decoration: none;
}

.nav-menu {
    display: flex;
    gap: 20px;
}

.nav-link {
    color: #666;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.nav-link:hover,
.nav-link.active {
    background: #007bff;
    color: white;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 15px;
}

.nav-username {
    color: #333;
    font-weight: 500;
}

.nav-logout-form {
    display: inline;
}

.nav-logout-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.nav-logout-btn:hover {
    background: #c82333;
}

@media (max-width: 768px) {
    .nav-container {
        flex-direction: column;
        height: auto;
        padding: 15px 20px;
    }
    
    .nav-menu {
        margin: 15px 0;
        flex-wrap: wrap;
    }
    
    .nav-user {
        margin-top: 10px;
    }
}
</style>
