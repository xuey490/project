import { motion, type Variants, type HTMLMotionProps } from 'framer-motion';
import { forwardRef, type ReactNode } from 'react';

// ── Shared easing & duration tokens ──
const ease = [0.25, 0.46, 0.45, 0.94] as const;
const springBounce = { type: 'spring', damping: 20, stiffness: 300 } as const;

// ── Variant factories ──
export const fadeUp: Variants = {
  hidden: { opacity: 0, y: 32 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.6, ease } },
};

export const fadeDown: Variants = {
  hidden: { opacity: 0, y: -24 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease } },
};

export const fadeIn: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { duration: 0.5, ease } },
};

export const fadeLeft: Variants = {
  hidden: { opacity: 0, x: -32 },
  visible: { opacity: 1, x: 0, transition: { duration: 0.6, ease } },
};

export const fadeRight: Variants = {
  hidden: { opacity: 0, x: 32 },
  visible: { opacity: 1, x: 0, transition: { duration: 0.6, ease } },
};

export const scaleUp: Variants = {
  hidden: { opacity: 0, scale: 0.92 },
  visible: { opacity: 1, scale: 1, transition: { duration: 0.5, ease } },
};

export const blurIn: Variants = {
  hidden: { opacity: 0, filter: 'blur(12px)' },
  visible: { opacity: 1, filter: 'blur(0px)', transition: { duration: 0.6, ease } },
};

// ── Stagger container ──
export const staggerContainer = (stagger = 0.1, delay = 0): Variants => ({
  hidden: {},
  visible: {
    transition: {
      staggerChildren: stagger,
      delayChildren: delay,
    },
  },
});

// ── Generic viewport-triggered wrapper ──
interface FadeInProps extends HTMLMotionProps<'div'> {
  children: ReactNode;
  variants?: Variants;
  delay?: number;
  duration?: number;
  className?: string;
  once?: boolean;
  amount?: number;
}

export const FadeIn = forwardRef<HTMLDivElement, FadeInProps>(
  ({ children, variants = fadeUp, delay = 0, duration, className, once = true, amount = 0.2, ...props }, ref) => (
    <motion.div
      ref={ref}
      variants={variants}
      initial="hidden"
      whileInView="visible"
      viewport={{ once, amount }}
      transition={delay || duration ? { delay, ...(duration ? { duration } : {}) } : undefined}
      className={className}
      {...props}
    >
      {children}
    </motion.div>
  ),
);
FadeIn.displayName = 'FadeIn';

// ── Stagger parent (triggers children) ──
interface StaggerProps extends HTMLMotionProps<'div'> {
  children: ReactNode;
  stagger?: number;
  delay?: number;
  className?: string;
  once?: boolean;
  amount?: number;
}

export const Stagger = forwardRef<HTMLDivElement, StaggerProps>(
  ({ children, stagger = 0.1, delay = 0, className, once = true, amount = 0.15, ...props }, ref) => (
    <motion.div
      ref={ref}
      variants={staggerContainer(stagger, delay)}
      initial="hidden"
      whileInView="visible"
      viewport={{ once, amount }}
      className={className}
      {...props}
    >
      {children}
    </motion.div>
  ),
);
Stagger.displayName = 'Stagger';

// ── Hover-lift card wrapper ──
interface HoverLiftProps extends HTMLMotionProps<'div'> {
  children: ReactNode;
  className?: string;
  lift?: number;
}

export const HoverLift = forwardRef<HTMLDivElement, HoverLiftProps>(
  ({ children, className, lift = -4, ...props }, ref) => (
    <motion.div
      ref={ref}
      variants={fadeUp}
      whileHover={{ y: lift, transition: { duration: 0.25, ease: 'easeOut' } }}
      className={className}
      {...props}
    >
      {children}
    </motion.div>
  ),
);
HoverLift.displayName = 'HoverLift';

// Re-export motion for convenience
export { motion, springBounce };
