<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h4>{{ __('messages.about') }}</h4>
            <p>{{ config('app.name', 'Application') }}</p>
            <p>{{ __('messages.footer_description') }}</p>
        </div>
        
        <div class="footer-section">
            <h4>{{ __('messages.quick_links') }}</h4>
            <ul class="footer-links">
                <li><a href="{{ url('/') }}">{{ __('messages.home') }}</a></li>
                @if(auth)
                    <li><a href="{{ route('profile') }}">{{ __('messages.profile') }}</a></li>
                    <li><a href="{{ route('settings') }}">{{ __('messages.settings') }}</a></li>
                @else
                    <li><a href="{{ route('admin.login') }}">{{ __('messages.admin_login') }}</a></li>
                    <li><a href="{{ route('customer.login') }}">{{ __('messages.customer_login') }}</a></li>
                @endif
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>{{ __('messages.contact') }}</h4>
            <p>{{ __('messages.email') }}: {{ config('mail.from_email', 'contact@example.com') }}</p>
            <p>{{ __('messages.phone') }}: {{ config('app.phone', '+55 11 9999-9999') }}</p>
        </div>
        
        <div class="footer-section">
            <h4>{{ __('messages.follow_us') }}</h4>
            <div class="social-links">
                <a href="{{ config('app.facebook', '#') }}" class="social-link facebook">Facebook</a>
                <a href="{{ config('app.twitter', '#') }}" class="social-link twitter">Twitter</a>
                <a href="{{ config('app.instagram', '#') }}" class="social-link instagram">Instagram</a>
                <a href="{{ config('app.linkedin', '#') }}" class="social-link linkedin">LinkedIn</a>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Application') }}. {{ __('messages.all_rights_reserved') }}</p>
        <p>
            {{ __('messages.powered_by') }} 
            <a href="https://github.com/yourusername/minimal-php-framework" target="_blank">
                Minimal PHP Framework
            </a>
        </p>
    </div>
</footer>

<style>
.site-footer {
    background: #333;
    color: #fff;
    padding: 40px 0 20px;
    margin-top: 50px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.footer-section h4 {
    color: #fff;
    margin-bottom: 15px;
    font-size: 18px;
}

.footer-section p {
    color: #ccc;
    line-height: 1.6;
    margin-bottom: 10px;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 8px;
}

.footer-links a {
    color: #ccc;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #fff;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-link {
    color: #ccc;
    text-decoration: none;
    padding: 8px 12px;
    border: 1px solid #555;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.social-link:hover {
    color: #fff;
    border-color: #777;
    background: rgba(255,255,255,0.1);
}

.footer-bottom {
    border-top: 1px solid #555;
    padding-top: 20px;
    text-align: center;
    color: #ccc;
}

.footer-bottom p {
    margin: 5px 0;
    font-size: 14px;
}

.footer-bottom a {
    color: #007bff;
    text-decoration: none;
}

.footer-bottom a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .social-links {
        justify-content: center;
    }
}
</style>
