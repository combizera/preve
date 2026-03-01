# Preve Style Guide

Guia visual e de componentes do projeto Preve. Use como referencia para manter consistencia visual ou iniciar novos projetos com a mesma identidade.

---

## Stack Visual

- **Tailwind CSS v4** (com `@theme inline` e CSS variables)
- **shadcn/ui** (adaptado com customizacoes)
- **Lucide Icons** (via `lucide-vue-next`)
- **Fonte**: Instrument Sans (fallback: Figtree, system-ui)
- **Dark mode**: class-based (`.dark`)

---

## 1. Design Tokens (CSS Variables)

### Cores Principais

| Token | Light | Dark | Uso |
|---|---|---|---|
| `--primary` | `hsl(221.2 83.2% 53.3%)` | `hsl(221.2 83.2% 53.3%)` | Botoes, links, foco |
| `--destructive` | `hsl(0 72.2% 50.6%)` | `hsl(0 72.2% 50.6%)` | Acoes destrutivas, erros |
| `--positive` | `hsl(97, 57%, 39%)` | `hsl(97, 57%, 39%)` | Receitas, sucesso |
| `--background` | `hsl(0 0% 100%)` | `hsl(0 0% 3.9%)` | Fundo geral |
| `--foreground` | `hsl(0 0% 3.9%)` | `hsl(0 0% 98%)` | Texto principal |
| `--muted` | `hsl(0 0% 96.1%)` | `hsl(0 0% 16.08%)` | Fundos sutis |
| `--muted-foreground` | `hsl(0 0% 45.1%)` | `hsl(0 0% 63.9%)` | Texto secundario |
| `--border` | `hsl(0 0% 92.8%)` | `hsl(0 0% 14.9%)` | Bordas |
| `--input` | `hsl(0 0% 89.8%)` | `hsl(0 0% 14.9%)` | Inputs |
| `--accent` | `oklch(0.967 0.001 286.375)` | `oklch(0.274 0.006 286.033)` | Hover de itens |
| `--sidebar` | `hsl(0 0% 98%)` | `hsl(0 0% 7%)` | Background da sidebar e headers de tabela |

### Border Radius (Sistema de Tokens)

```css
--radius: 0.5rem;                             /* 8px - base */
--radius-lg: var(--radius);                   /* 8px */
--radius-md: calc(var(--radius) - 2px);       /* 6px */
--radius-sm: calc(var(--radius) - 4px);       /* 4px */

/* "Outer" radius - para wrappers que envolvem elementos arredondados */
--radius-sm-outer: calc(var(--radius-sm) + 4px);   /* 8px */
--radius-md-outer: calc(var(--radius-md) + 4px);   /* 10px */
--radius-lg-outer: calc(var(--radius-lg) + 4px);   /* 12px */
--radius-xl-outer: calc(var(--radius-xl) + 4px);   /* ~16px */
```

> **Conceito "outer radius"**: Quando um container tem `padding` e envolve um elemento com border-radius, o container precisa de um radius maior para que a distancia entre as bordas fique visualmente uniforme. Essa tecnica e usada nas tabelas e dialogs.

### Cores de Graficos

```css
--chart-1: hsl(221.2 83.2% 53.3%);   /* Azul (Primary) */
--chart-2: hsl(173 58% 39%);          /* Teal */
--chart-3: hsl(197 37% 24%);          /* Azul Escuro */
--chart-4: hsl(43 74% 66%);           /* Amarelo */
--chart-5: hsl(27 87% 67%);           /* Laranja */
```

---

## 2. Botoes (O Diferencial do Projeto)

Os botoes sao o destaque visual do projeto. Usam **gradiente vertical** (`bg-gradient-to-t`) com **sombra colorida** e efeitos de **brightness** no hover/active.

### Variante Default (Primary)

```
from-primary to-primary/85
text-primary-foreground
border border-zinc-950/25
bg-gradient-to-t
shadow-sm shadow-zinc-950/20
transition-[filter] duration-200
hover:brightness-110
active:brightness-90
dark:border-white/20
```

**O que torna especial:**
- `bg-gradient-to-t` + `from-primary to-primary/85` cria um gradiente sutil de baixo para cima (mais escuro na base, mais claro no topo), dando sensacao de profundidade/3D
- `shadow-sm shadow-zinc-950/20` adiciona sombra escura semi-transparente
- `border border-zinc-950/25` borda escura semi-transparente que define a borda sem parecer "flat"
- `hover:brightness-110` no hover o botao inteiro fica mais brilhante (nao muda cor, muda luminosidade)
- `active:brightness-90` no click o botao escurece, simulando que foi "pressionado"
- No dark mode: `dark:border-white/20` borda clara sutil

### Variante Destructive

```
from-destructive to-destructive/85
text-destructive-foreground
border border-zinc-950/25
bg-gradient-to-t
shadow-sm shadow-zinc-950/20
transition-[filter] duration-200
hover:brightness-110
active:brightness-90
dark:border-white/15
```

Mesma tecnica do default, mas com cor `destructive` (vermelho).

### Variante Outline

```
bg-muted
hover:bg-background
dark:bg-muted/25
dark:hover:bg-muted/50
dark:border-border
inset-shadow-2xs inset-shadow-white
dark:inset-shadow-transparent
border border-zinc-300
shadow-sm shadow-zinc-950/10
duration-150
```

**O que torna especial:**
- `inset-shadow-2xs inset-shadow-white` cria uma sombra interna branca muito sutil no topo, dando efeito de "vidro" / "glass"
- No dark mode a inset shadow some (`dark:inset-shadow-transparent`)
- Sombra externa mais sutil que o default (`shadow-zinc-950/10` vs `/20`)

### Variante Secondary

```
from-secondary to-secondary/85
text-secondary-foreground
bg-gradient-to-t
shadow-sm shadow-zinc-950/10
transition-[filter] duration-200
hover:brightness-110
active:brightness-90
```

Mesma tecnica de gradiente, com cores neutras.

### Variante Ghost

```
hover:bg-accent
hover:text-accent-foreground
dark:hover:bg-background/60
```

Sem fundo, sem borda. Aparece apenas no hover.

### Variante Link

```
text-primary
underline-offset-4
hover:underline
```

Estilo de link simples.

### Tamanhos

| Size | Classes | Uso |
|---|---|---|
| `default` | `h-9 px-4 py-2 has-[>svg]:px-3` | Padrao |
| `sm` | `h-8 rounded gap-1.5 px-2 has-[>svg]:px-2` | Botoes menores |
| `lg` | `h-10 rounded px-6 has-[>svg]:px-4` | Destaque |
| `icon` | `size-9` | Botao quadrado com icone |
| `icon-sm` | `size-8` | Icone pequeno |
| `icon-lg` | `size-10` | Icone grande |

### Classes Base (Todas as Variantes)

```
inline-flex items-center justify-center gap-2
whitespace-nowrap rounded text-sm font-medium
transition-all
disabled:pointer-events-none disabled:opacity-50
[&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4
shrink-0 [&_svg]:shrink-0
outline-none
focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]
aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40
aria-invalid:border-destructive
hover:cursor-pointer
```

---

## 3. Tabelas (Double Border)

A tabela usa uma tecnica de **double border** com padding entre os layers, criando um efeito de "moldura":

### Estrutura

```html
<!-- Wrapper externo: borda + padding -->
<div class="p-1 border rounded-lg-outer">
  <!-- Wrapper interno: borda + overflow -->
  <div class="relative w-full overflow-auto border rounded-lg">
    <!-- Tabela -->
    <table class="w-full caption-bottom text-sm">
      ...
    </table>
  </div>
</div>
```

**O que torna especial:**
- O wrapper externo tem `p-1` (4px de padding) + `border` + `rounded-lg-outer`
- O wrapper interno tem `border` + `rounded-lg` (radius menor que o outer)
- O `p-1` entre as duas bordas cria um "gap" visual que parece uma moldura elegante
- `rounded-lg-outer` e calculado como `rounded-lg + 4px`, mantendo a curva proporcional

### Header

```
[&_tr]:border-b [&_tr]:bg-sidebar
```

O header da tabela usa `bg-sidebar` (um cinza muito claro no light / escuro no dark), diferenciando visualmente do body.

### Rows

```
hover:bg-muted/50
data-[state=selected]:bg-muted
border-b
transition-colors
```

### Cells

```
/* Head */
text-muted-foreground h-10 px-4 text-left align-middle font-medium whitespace-nowrap

/* Body */
px-4 py-2 align-middle whitespace-nowrap
```

---

## 4. Dialogs (Glassmorphism)

Os dialogs usam efeito de **glassmorphism** com camada dupla:

### Estrutura

```html
<!-- Camada externa: blur + semi-transparente -->
<div class="
  bg-background/60 dark:bg-muted/70
  p-1 rounded-xl-outer
  border border-background/10 dark:border-muted/50
  shadow-lg backdrop-blur-sm
">
  <!-- Camada interna: solida -->
  <div class="bg-background border p-6 rounded-xl shadow">
    <!-- Conteudo -->
  </div>
</div>
```

**O que torna especial:**
- Camada externa: `bg-background/60` (60% opaco) + `backdrop-blur-sm` = efeito vidro
- `p-1` entre as camadas cria o mesmo efeito "moldura" das tabelas
- `rounded-xl-outer` no wrapper, `rounded-xl` no conteudo (proporcional)
- `border-background/10` borda quase invisivel que define a borda do "vidro"
- No dark mode: `bg-muted/70` + `border-muted/50` = vidro mais escuro

### Animacoes

```
data-[state=open]:animate-in
data-[state=closed]:animate-out
data-[state=closed]:fade-out-0
data-[state=open]:fade-in-0
data-[state=closed]:zoom-out-95
data-[state=open]:zoom-in-95
duration-200
```

Abre com fade-in + zoom-in (de 95% para 100%), fecha com o inverso.

### Header / Footer

```
/* Header */
flex flex-col gap-2 text-center sm:text-left

/* Title */
text-lg leading-none font-semibold

/* Description */
text-muted-foreground text-sm

/* Footer */
flex mt-4 flex-col-reverse gap-2 sm:flex-row sm:justify-end
```

---

## 5. Inputs

### Input Base

```
h-9 w-full min-w-0
rounded-md border bg-transparent
px-3 py-1 text-base md:text-sm
shadow-xs
border-input
dark:bg-input/30
placeholder:text-muted-foreground
selection:bg-primary selection:text-primary-foreground
transition-[color,box-shadow]
outline-none
```

### Estados

```
/* Focus */
focus-visible:border-ring
focus-visible:ring-ring/50
focus-visible:ring-[3px]

/* Erro */
aria-invalid:ring-destructive/20
dark:aria-invalid:ring-destructive/40
aria-invalid:border-destructive

/* Disabled */
disabled:pointer-events-none
disabled:cursor-not-allowed
disabled:opacity-50
```

### Input Error Text

```
text-sm text-red-600 dark:text-red-500
```

### Select Trigger

```
hover:cursor-pointer
border-input
dark:bg-input/30 dark:hover:bg-input/50
rounded-md border bg-transparent
px-3 py-2 text-sm
shadow-xs
```

### Textarea

```
min-h-16 w-full
rounded-md border bg-transparent
px-3 py-2 text-base md:text-sm
shadow-xs
field-sizing-content    /* altura automatica pelo conteudo */
```

---

## 6. Toggle Group

Usado para selecao de tipo (Income/Expense):

```
/* Container */
inline-flex h-10 items-center justify-center
rounded-md bg-muted p-1 text-muted-foreground

/* Item */
rounded-sm px-3 py-1.5 text-sm font-medium
ring-offset-background transition-all

/* Item Selecionado */
bg-background text-foreground shadow-sm

/* Item Hover */
hover:bg-muted hover:text-foreground
```

O container `bg-muted` com `p-1` cria o mesmo padrao de "moldura" dos outros componentes.

---

## 7. Badge

```
/* Base */
inline-flex items-center justify-center
rounded-full border
px-2 py-0.5 text-xs font-medium
transition-[color,box-shadow]

/* Variantes */
default:     border-transparent bg-primary text-primary-foreground
secondary:   border-transparent bg-secondary text-secondary-foreground
destructive: border-transparent bg-destructive text-white
outline:     text-foreground (apenas borda)
```

---

## 8. Cards

```
bg-card text-card-foreground
flex flex-col gap-6
rounded-xl border py-6
shadow-sm
```

### Header

```
grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6
```

### Content

```
px-6
```

---

## 9. Sheet (Painel Lateral)

```
/* Base */
bg-background
fixed z-50 flex flex-col gap-4
shadow-lg transition ease-in-out

/* Direcao: Right (padrao) */
inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm

/* Animacao */
data-[state=closed]:duration-300
data-[state=open]:duration-500
data-[state=closed]:slide-out-to-right
data-[state=open]:slide-in-from-right
```

---

## 10. Sistema de Cores para Categorias

16 cores disponiveis, cada uma com 4 variantes:

| Variante | Light | Dark | Uso |
|---|---|---|---|
| `bg` | `bg-{color}-200` | `bg-{color}-900` | Fundo do badge |
| `picker` | `bg-{color}-500` | `bg-{color}-600` | Seletor de cor |
| `text` | `text-{color}-500` | `text-{color}-300` | Texto/icone |
| `border` | `border-{color}-300` | `border-{color}-800` | Borda |

**Cores**: red, orange, amber, yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink.

Cada cor sempre tem as 4 variantes para garantir contraste em light e dark mode.

---

## 11. Layout

### Page Container

```css
.page-container {
    @apply mx-auto flex h-full w-full max-w-[1500px] flex-1 flex-col gap-2 overflow-x-auto rounded-xl p-4;
}
```

### Heading

```
/* Container */
mt-4 mb-8 flex items-center justify-between

/* Title */
text-xl font-semibold tracking-tight

/* Description */
text-sm text-muted-foreground
```

### Quick Create Card

```
flex flex-col rounded-lg-outer border bg-sidebar p-1
```

Mesma tecnica de double border: `bg-sidebar` no wrapper externo, conteudo interno com `border rounded-lg bg-background`.

### Empty State

```
flex flex-col items-center justify-center gap-4 bg-sidebar p-8 text-center
```

---

## 12. Espacamento

### Padroes Recorrentes

| Contexto | Gap / Padding |
|---|---|
| Entre items de lista | `gap-2` (8px) |
| Entre secoes de form | `gap-4` (16px) |
| Card padding | `p-6` (24px) / `py-6` |
| Dialog padding | `p-6` (24px) |
| Page padding | `p-4` (16px) |
| Moldura (outer wrapper) | `p-1` (4px) |
| Heading margin | `mt-4 mb-8` |
| Button gap interno | `gap-2` |
| Cell padding | `px-4 py-2` |

---

## 13. Sombras

| Classe | Uso |
|---|---|
| `shadow-xs` | Inputs, checkboxes |
| `shadow-sm shadow-zinc-950/20` | Botoes default/destructive |
| `shadow-sm shadow-zinc-950/10` | Botoes outline/secondary |
| `shadow-sm` | Cards, toggle group items selecionados |
| `shadow-lg` | Dialogs, sheets |
| `inset-shadow-2xs inset-shadow-white` | Botao outline (efeito glass interno) |

---

## 14. Animacoes e Transicoes

### Duracoes

| Duracao | Uso |
|---|---|
| `duration-150` | Botao outline hover |
| `duration-200` | Botoes gradient, dialogs |
| `duration-300` | Sheet close |
| `duration-500` | Sheet open |

### Tipos de Transicao

```
transition-all               /* Geral */
transition-[filter]          /* Botoes gradient (brightness) */
transition-[color,box-shadow] /* Inputs, badges */
transition-colors            /* Table rows */
transition-opacity           /* Dialog close button */
transition ease-in-out       /* Sheets */
```

### Animacoes de Entrada/Saida

```
/* Fade + Zoom (Dialogs, Selects, Dropdowns) */
animate-in / animate-out
fade-in-0 / fade-out-0
zoom-in-95 / zoom-out-95

/* Slide (Sheets, Dropdowns posicionais) */
slide-in-from-right / slide-out-to-right
slide-in-from-top-2 / slide-in-from-bottom-2
```

---

## 15. Focus e Acessibilidade

### Padrao de Focus

```
outline-none
focus-visible:border-ring
focus-visible:ring-ring/50
focus-visible:ring-[3px]
```

Ring de 3px com cor `ring` a 50% de opacidade. Borda muda para `border-ring`.

### Padrao de Erro

```
aria-invalid:ring-destructive/20
dark:aria-invalid:ring-destructive/40
aria-invalid:border-destructive
```

Usa `aria-invalid` para estilizar campos com erro, sem classes manuais.

### Padrao de Disabled

```
disabled:pointer-events-none
disabled:cursor-not-allowed
disabled:opacity-50
```

---

## 16. Padroes Visuais Recorrentes (Resumo)

### 1. Double Border / Moldura

Usado em: **Tabelas, Dialogs, Quick Create Cards, Toggle Groups**

```
/* Wrapper externo */
p-1 border rounded-{size}-outer bg-{color}

/* Conteudo interno */
border rounded-{size} bg-background
```

O `p-1` entre as bordas cria um "gap" visual. O radius outer e sempre `radius + 4px`.

### 2. Gradiente + Brightness

Usado em: **Botoes default, destructive, secondary**

```
bg-gradient-to-t from-{color} to-{color}/85
shadow-sm shadow-zinc-950/20
border border-zinc-950/25
hover:brightness-110
active:brightness-90
```

### 3. Glassmorphism

Usado em: **Dialogs**

```
bg-{color}/60 backdrop-blur-sm
border border-{color}/10
```

### 4. Sidebar Background

Usado em: **Table headers, Quick Create wrappers, Empty States**

`bg-sidebar` como fundo sutil diferenciado do `bg-background`.

---

## 17. Como Reproduzir em um Novo Projeto

1. **Instalar shadcn/ui** com a configuracao padrao
2. **Copiar as CSS variables** da secao 1 para seu `app.css`
3. **Customizar o Button** (`components/ui/button/index.ts`) com as variantes de gradiente
4. **Customizar o Table** (`components/ui/table/Table.vue`) com a estrutura double border
5. **Customizar o DialogContent** com a camada de glassmorphism
6. **Adicionar os radius tokens** `--radius-*-outer` no `@theme`
7. **Usar `bg-sidebar`** para backgrounds sutis em headers e wrappers
8. A fonte **Instrument Sans** pode ser trocada, o importante e manter no `@layer utilities` para override

### Dependencias Visuais

```json
{
  "tw-animate-css": "^1.x",
  "class-variance-authority": "^0.7",
  "lucide-vue-next": "^0.x",
  "tailwindcss": "^4.x"
}
```
