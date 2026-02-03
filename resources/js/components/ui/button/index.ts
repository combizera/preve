import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive hover:cursor-pointer",
  {
    variants: {
      variant: {
        default:
          "from-primary to-primary/85 text-primary-foreground border border-zinc-950/25 bg-gradient-to-t shadow-sm shadow-zinc-950/20 transition-[filter] duration-200 hover:brightness-110 active:brightness-90 dark:border-white/20",
        destructive:
          "from-destructive to-destructive/85 text-destructive-foreground border border-zinc-950/25 bg-gradient-to-t shadow-sm shadow-zinc-950/20 transition-[filter] duration-200 hover:brightness-110 active:brightness-90 dark:border-white/15 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40",
        outline:
          "bg-muted hover:bg-background dark:bg-muted/25 dark:hover:bg-muted/50 dark:border-border inset-shadow-2xs inset-shadow-white dark:inset-shadow-transparent relative flex border border-zinc-300 shadow-sm shadow-zinc-950/10 duration-150",
        secondary:
          "from-secondary to-secondary/85 text-secondary-foreground hover:bg-secondary/80 bg-gradient-to-t shadow-sm shadow-zinc-950/20 transition-[filter] duration-200 hover:brightness-110 active:brightness-90 shadow-sm shadow-zinc-950/10 duration-150",
        ghost:
          "hover:bg-accent hover:text-accent-foreground dark:hover:bg-background/60",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        "default": "h-9 px-4 py-2 has-[>svg]:px-3",
        "sm": "h-8 rounded gap-1.5 px-2 has-[>svg]:px-2",
        "lg": "h-10 rounded px-6 has-[>svg]:px-4",
        "icon": "size-9",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
