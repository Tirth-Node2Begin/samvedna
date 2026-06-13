import type { MouseEventHandler, ReactNode } from "react";
import { cn } from "@/lib/utils";

type ButtonVariant = "primary" | "secondary" | "ghost" | "light";
type ButtonSize = "sm" | "md" | "lg";

type BaseButtonProps = {
  "aria-label"?: string;
  children: ReactNode;
  className?: string;
  icon?: ReactNode;
  iconPosition?: "left" | "right";
  onClick?: MouseEventHandler<HTMLAnchorElement | HTMLButtonElement>;
  size?: ButtonSize;
  variant?: ButtonVariant;
};

type LinkButtonProps = BaseButtonProps & {
  href: string;
  rel?: string;
  target?: string;
};

type NativeButtonProps = BaseButtonProps & {
  disabled?: boolean;
  form?: string;
  href?: undefined;
  name?: string;
  type?: "button" | "submit" | "reset";
  value?: string;
};

type ButtonProps = LinkButtonProps | NativeButtonProps;

const variantClasses: Record<ButtonVariant, string> = {
  primary:
    "border-transparent bg-gradient-to-r from-primary to-secondary text-white hover:opacity-90 shadow-md",
  secondary:
    "border-border bg-white text-text hover:border-primary hover:text-primary",
  ghost:
    "border-border bg-transparent text-text hover:border-primary hover:bg-bg-soft hover:text-primary",
  light:
    "border-white/30 bg-white text-primary hover:border-white hover:bg-bg-soft"
};

const sizeClasses: Record<ButtonSize, string> = {
  sm: "h-10 px-4 text-sm",
  md: "h-12 px-5 text-[15px]",
  lg: "h-14 px-6 text-base"
};

function buttonClassName(
  variant: ButtonVariant,
  size: ButtonSize,
  className?: string
): string {
  return cn(
    "inline-flex items-center justify-center gap-2 rounded-control border font-semibold leading-none transition duration-200 disabled:pointer-events-none disabled:opacity-60",
    sizeClasses[size],
    variantClasses[variant],
    className
  );
}

function isLinkButton(props: ButtonProps): props is LinkButtonProps {
  return typeof props.href === "string";
}

export default function Button(props: ButtonProps) {
  const {
    children,
    className,
    icon,
    iconPosition = "right",
    size = "md",
    variant = "primary"
  } = props;

  const content = (
    <>
      {iconPosition === "left" ? icon : null}
      <span>{children}</span>
      {iconPosition === "right" ? icon : null}
    </>
  );

  if (isLinkButton(props)) {
    return (
      <a
        aria-label={props["aria-label"]}
        href={props.href}
        onClick={props.onClick}
        rel={props.rel}
        target={props.target}
        className={buttonClassName(variant, size, className)}
      >
        {content}
      </a>
    );
  }

  const nativeProps = props;

  return (
    <button
      aria-label={nativeProps["aria-label"]}
      disabled={nativeProps.disabled}
      form={nativeProps.form}
      name={nativeProps.name}
      onClick={nativeProps.onClick}
      suppressHydrationWarning
      type={nativeProps.type ?? "button"}
      value={nativeProps.value}
      className={buttonClassName(variant, size, className)}
    >
      {content}
    </button>
  );
}
