@if(hasMessages)
    <div class="flash-messages">
        @if(messages.success)
            @foreach(messages.success as $message)
                <div class="flash-message flash-success">
                    {{ $message }}
                    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endforeach
        @endif
        
        @if(messages.error)
            @foreach(messages.error as $message)
                <div class="flash-message flash-error">
                    {{ $message }}
                    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endforeach
        @endif
        
        @if(messages.warning)
            @foreach(messages.warning as $message)
                <div class="flash-message flash-warning">
                    {{ $message }}
                    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endforeach
        @endif
        
        @if(messages.info)
            @foreach(messages.info as $message)
                <div class="flash-message flash-info">
                    {{ $message }}
                    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
                </div>
            @endforeach
        @endif
    </div>
@endif

@if(hasErrors)
    <div class="validation-errors">
        <div class="error-summary">
            <h4>{{ __('messages.validation_errors') }}</h4>
            <ul>
                @foreach(errors as $field => $fieldErrors)
                    @foreach($fieldErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    </div>
@endif

<style>
.flash-messages {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;
}

.flash-message {
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    position: relative;
    animation: slideIn 0.3s ease-out;
}

.flash-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.flash-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.flash-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.flash-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.flash-close {
    position: absolute;
    top: 5px;
    right: 10px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
}

.flash-close:hover {
    opacity: 1;
}

.validation-errors {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
    padding: 20px;
    margin: 20px 0;
}

.error-summary h4 {
    color: #721c24;
    margin-top: 0;
    margin-bottom: 10px;
}

.error-summary ul {
    color: #721c24;
    margin: 0;
    padding-left: 20px;
}

.error-summary li {
    margin-bottom: 5px;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .flash-messages {
        left: 20px;
        right: 20px;
        max-width: none;
    }
}
</style>
