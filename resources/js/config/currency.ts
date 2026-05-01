export type SupportedCurrency = 'BRL' | 'USD';

interface CurrencyConfig {
    symbol: string;
    label: string;
    decimalSeparator: string;
    thousandSeparator: string;
}

export const CURRENCIES: Record<SupportedCurrency, CurrencyConfig> = {
    BRL: {
        symbol: 'R$',
        label: 'BRL - Real (R$)',
        decimalSeparator: ',',
        thousandSeparator: '.',
    },
    USD: {
        symbol: '$',
        label: 'USD - Dollar ($)',
        decimalSeparator: '.',
        thousandSeparator: ',',
    },
};

export const SUPPORTED_CURRENCIES = Object.entries(CURRENCIES).map(([value, config]) => ({
    value: value as SupportedCurrency,
    label: config.label,
}));
