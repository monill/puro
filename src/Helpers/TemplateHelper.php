<?php

namespace App\Helpers;

class TemplateHelper {
    /**
     * Obter tradução para template
     */
    public static function trans($key, $replace = []) {
        return LangHelper::get($key, $replace);
    }
    
    /**
     * Obter tradução plural para template
     */
    public static function transChoice($key, $number, $replace = []) {
        return LangHelper::choice($key, $number, $replace);
    }
    
    /**
     * Gerar seletor de idiomas
     */
    public static function languageSelector() {
        return LangHelper::languageSelector();
    }
    
    /**
     * Obter locale atual
     */
    public static function currentLocale() {
        return LangHelper::getLocale();
    }
    
    /**
     * Obter bandeira do locale
     */
    public static function localeFlag($locale = null) {
        $locale = $locale ?? LangHelper::getLocale();
        return LangHelper::getFlag($locale);
    }
    
    /**
     * Formatar número com locale
     */
    public static function formatNumber($number, $decimals = 0) {
        $locale = LangHelper::getLocale();
        
        if ($locale === 'pt-br') {
            return number_format($number, $decimals, ',', '.');
        } else {
            return number_format($number, $decimals, '.', ',');
        }
    }
    
    /**
     * Formatar moeda com locale
     */
    public static function formatCurrency($amount, $currency = 'USD') {
        $locale = LangHelper::getLocale();
        
        if ($locale === 'pt-br') {
            if ($currency === 'USD') {
                return 'US$ ' . self::formatNumber($amount, 2);
            } else {
                return 'R$ ' . self::formatNumber($amount, 2);
            }
        } else {
            return '$' . self::formatNumber($amount, 2);
        }
    }
    
    /**
     * Formatar data com locale
     */
    public static function formatDate($date, $format = 'medium') {
        $locale = LangHelper::getLocale();
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        
        if ($locale === 'pt-br') {
            switch ($format) {
                case 'short':
                    return date('d/m/Y', $timestamp);
                case 'medium':
                    return date('d/m/Y H:i', $timestamp);
                case 'long':
                    return date('d \d\e F \d\e Y', $timestamp);
                default:
                    return date('d/m/Y H:i:s', $timestamp);
            }
        } else {
            switch ($format) {
                case 'short':
                    return date('m/d/Y', $timestamp);
                case 'medium':
                    return date('M d, Y H:i', $timestamp);
                case 'long':
                    return date('F d, Y', $timestamp);
                default:
                    return date('Y-m-d H:i:s', $timestamp);
            }
        }
    }
    
    /**
     * Formatar tempo relativo com locale
     */
    public static function timeAgo($date) {
        $locale = LangHelper::getLocale();
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        $now = time();
        $diff = $now - $timestamp;
        
        if ($diff < 60) {
            return self::trans('common.now');
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return self::transChoice('common.minutes_ago', $minutes);
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return self::transChoice('common.hours_ago', $hours);
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return self::transChoice('common.days_ago', $days);
        } else {
            return self::formatDate($date, 'medium');
        }
    }
    
    /**
     * Obter nome do dia da semana
     */
    public static function dayName($day, $short = false) {
        $locale = LangHelper::getLocale();
        
        if ($locale === 'pt-br') {
            $days = [
                0 => 'Domingo', 1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta',
                4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado'
            ];
            $shortDays = [
                0 => 'Dom', 1 => 'Seg', 2 => 'Ter', 3 => 'Qua',
                4 => 'Qui', 5 => 'Sex', 6 => 'Sáb'
            ];
        } else {
            $days = [
                0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
            ];
            $shortDays = [
                0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed',
                4 => 'Thu', 5 => 'Fri', 6 => 'Sat'
            ];
        }
        
        return $short ? $shortDays[$day] : $days[$day];
    }
    
    /**
     * Obter nome do mês
     */
    public static function monthName($month, $short = false) {
        $locale = LangHelper::getLocale();
        
        if ($locale === 'pt-br') {
            $months = [
                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
            ];
            $shortMonths = [
                1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
                5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
                9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
            ];
        } else {
            $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];
            $shortMonths = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
            ];
        }
        
        return $short ? $shortMonths[$month] : $months[$month];
    }
    
    /**
     * Direção do texto (RTL/LTR)
     */
    public static function textDirection() {
        $locale = LangHelper::getLocale();
        $rtlLocales = ['ar', 'he', 'fa', 'ur'];
        
        return in_array($locale, $rtlLocales) ? 'rtl' : 'ltr';
    }
    
    /**
     * Alinhamento do texto baseado no locale
     */
    public static function textAlign($align = 'left') {
        $direction = self::textDirection();
        
        if ($direction === 'rtl') {
            switch ($align) {
                case 'left': return 'right';
                case 'right': return 'left';
                default: return $align;
            }
        }
        
        return $align;
    }
}
