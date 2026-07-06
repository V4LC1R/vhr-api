import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"
import markDark from '@/assets/logo/vhr-mark-mono-dark.svg';
import markLight from '@/assets/logo/vhr-mark-mono-light.svg';
import mardSeal from '@/assets/logo/vhr-seal-reverse.svg';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export const logo ={
  dark:markDark,
  light:markLight,
  gold:mardSeal
}