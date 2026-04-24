<script setup lang="ts">
import Navbar from "@/components/Navbar.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    Wallet,
    Target,
    BarChart3,
    Shield,
    Zap,
    PiggyBank,
    ArrowRight,
    Check,
    Star,
    ChevronRight,
} from "lucide-vue-next";
import { ref } from "vue";

const features = [
    {
        icon: Wallet,
        title: "Multi-Account Management",
        description:
            "Track all your bank accounts, credit cards, and e-wallets in one centralized dashboard.",
    },
    {
        icon: BarChart3,
        title: "Smart Analytics",
        description:
            "Get detailed insights into your spending patterns with beautiful charts and reports.",
    },
    {
        icon: Target,
        title: "Goal Tracking",
        description:
            "Set savings goals and watch your progress with visual milestones and achievements.",
    },
    {
        icon: PiggyBank,
        title: "Budget Planning",
        description:
            "Create custom budgets for each category and stay on track with real-time alerts.",
    },
    {
        icon: Shield,
        title: "Secure & Private",
        description:
            "Your financial data is encrypted and stored securely. We never share your information.",
    },
    {
        icon: Zap,
        title: "Fast & Intuitive",
        description:
            "Lightning-fast performance with a clean interface designed for effortless navigation.",
    },
];

const steps = [
    {
        number: "01",
        title: "Create Your Account",
        description: "Sign up in seconds and set up your profile with your financial goals.",
    },
    {
        number: "02",
        title: "Add Your Accounts",
        description: "Connect or manually add your bank accounts, cards, and wallets.",
    },
    {
        number: "03",
        title: "Track Everything",
        description:
            "Log transactions, set budgets, and watch your wealth grow over time.",
    },
];

const testimonials = [
    {
        name: "Sarah Chen",
        role: "Freelance Designer",
        avatar: "SC",
        content:
            "Nabungo transformed how I manage my finances. The budget tracking feature helped me save 30% more each month!",
        rating: 5,
    },
    {
        name: "Michael Roberts",
        role: "Software Engineer",
        avatar: "MR",
        content:
            "Finally, a financial app that's both powerful and beautiful. The analytics dashboard is incredibly insightful.",
        rating: 5,
    },
    {
        name: "Amanda Williams",
        role: "Small Business Owner",
        avatar: "AW",
        content:
            "I've tried many finance apps, but Nabungo's goal tracking feature keeps me motivated like no other.",
        rating: 5,
    },
];

const faqs = ref([
    {
        question: "Is Nabungo free to use?",
        answer:
            "Yes! Nabungo offers a generous free tier with all essential features. Premium features are available for power users.",
        open: false,
    },
    {
        question: "How secure is my financial data?",
        answer:
            "We use bank-level encryption (AES-256) to protect your data. Your information is never shared with third parties.",
        open: false,
    },
    {
        question: "Can I export my data?",
        answer:
            "Absolutely! You can export all your transactions, reports, and analytics in various formats including CSV and PDF.",
        open: false,
    },
    {
        question: "Does Nabungo support multiple currencies?",
        answer:
            "Yes, Nabungo supports multiple currencies and automatically handles conversions for your convenience.",
        open: false,
    },
]);

const toggleFaq = (index: number) => {
    faqs.value[index].open = !faqs.value[index].open;
};
</script>

<template>

    <Head title="Nabungo - Smart Financial Tracker">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-[#08070E] text-white overflow-x-hidden">
        <!-- Background Effects -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <!-- Top-left large orb -->
            <div
                class="absolute -left-[300px] -top-[300px] h-[800px] w-[800px] rounded-full bg-primary opacity-[0.12] blur-[140px]">
            </div>
            <!-- Top-right accent -->
            <div
                class="absolute -right-[200px] top-[50px] h-[600px] w-[600px] rounded-full bg-[oklch(0.65_0.22_260)] opacity-[0.07] blur-[130px]">
            </div>
            <!-- Center subtle glow -->
            <div
                class="absolute left-1/2 top-1/3 -translate-x-1/2 h-[500px] w-[900px] rounded-full bg-primary opacity-[0.04] blur-[160px]">
            </div>
            <!-- Bottom accent -->
            <div
                class="absolute -bottom-[200px] right-1/3 h-[500px] w-[500px] rounded-full bg-[oklch(0.70_0.22_128)] opacity-[0.08] blur-[120px]">
            </div>
            <!-- Noise texture overlay -->
            <div class="absolute inset-0 opacity-[0.015]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E'); background-size: 200px;"></div>
        </div>

        <Navbar />

        <!-- Hero Section -->
        <section id="hero" class="relative min-h-screen flex flex-col items-center justify-center px-4 pb-20">
            <div class="max-w-5xl mx-auto text-center z-10">
                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-primary/15 to-[oklch(0.70_0.22_128)]/15 border border-primary/30 mb-8 animate-fade-in backdrop-blur-sm shadow-[0_0_20px_oklch(0.648_0.2_131.684/0.15)]">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                    </span>
                    <span class="text-sm font-semibold bg-gradient-to-r from-primary to-[oklch(0.72_0.20_128)] bg-clip-text text-transparent">Your Personal Finance Companion</span>
                </div>

                <!-- Main Heading -->
                <h1 class="font-dm-sans text-5xl md:text-7xl font-bold tracking-tight max-w-4xl leading-[1.1] mb-6 mx-auto">
                    Take Control of Your
                    <br class="hidden md:block" />
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-[oklch(0.78_0.22_110)] to-[oklch(0.72_0.24_155)] drop-shadow-[0_0_30px_oklch(0.648_0.2_131.684/0.5)]">
                        Financial Future
                    </span>
                </h1>

                <p class="font-dm-sans text-[#9A9AA8] text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
                    Nabungo helps you track expenses, set budgets, achieve savings goals, and gain
                    complete control over your money with beautiful insights and smart analytics.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                    <Link :href="route('register')">
                        <button
                            class="group relative px-8 py-4 rounded-full font-bold tracking-wide font-dm-sans text-black cursor-pointer transition-all duration-300 hover:scale-105 flex items-center gap-2 overflow-hidden bg-gradient-to-r from-primary via-[oklch(0.70_0.22_128)] to-primary bg-size-200 hover:bg-right shadow-[0_4px_30px_oklch(0.648_0.2_131.684/0.5)] hover:shadow-[0_4px_50px_oklch(0.648_0.2_131.684/0.7)]">
                            <span class="relative z-10 flex items-center gap-2">
                                Start Free Today
                                <ArrowRight :size="20" class="group-hover:translate-x-1 transition-transform" />
                            </span>
                        </button>
                    </Link>
                    <a href="#features">
                        <button
                            class="px-8 py-4 rounded-full bg-white/5 backdrop-blur-sm border border-white/15 font-medium font-dm-sans text-white cursor-pointer transition-all duration-300 hover:border-primary/50 hover:bg-primary/10 hover:shadow-[0_0_20px_oklch(0.648_0.2_131.684/0.15)]">
                            Explore Features
                        </button>
                    </a>
                </div>

            </div>

            <!-- Dashboard Preview -->
            <div class="relative w-full max-w-6xl mx-auto mt-4 px-4">
                <!-- Outer glow rings -->
                <div class="absolute -inset-8 bg-gradient-to-r from-primary/20 via-[oklch(0.70_0.22_128)]/10 to-primary/20 rounded-3xl blur-2xl opacity-60"></div>
                <div class="absolute -inset-4 bg-gradient-to-r from-primary/10 via-transparent to-[oklch(0.70_0.22_128)]/10 rounded-2xl blur-xl"></div>
                <div class="relative rounded-2xl overflow-hidden shadow-[0_40px_100px_rgba(0,0,0,0.8)] border border-white/10">
                    <!-- Top border glow -->
                    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/60 to-transparent"></div>
                    <img src="/images/dashboard.png" alt="Nabungo Dashboard" class="w-full h-auto" />
                    <div class="absolute inset-0 bg-gradient-to-t from-[#08070E] via-[#08070E]/10 to-transparent">
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="relative py-32 px-4">
            <!-- Section glow -->
            <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-px bg-gradient-to-r from-transparent via-primary/20 to-transparent"></div>
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-20">
                    <span class="inline-block text-primary text-xs font-bold tracking-[0.25em] uppercase mb-4 px-3 py-1 rounded-full border border-primary/30 bg-primary/10">Features</span>
                    <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6 leading-tight">
                        Everything You Need to
                        <br />
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-[oklch(0.78_0.22_110)] to-[oklch(0.72_0.24_155)]">Master
                            Your Money</span>
                    </h2>
                    <p class="text-[#9A9AA8] text-lg max-w-2xl mx-auto">
                        Powerful features designed to simplify your financial life and help you
                        achieve your goals faster.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="feature in features" :key="feature.title"
                        class="group relative p-8 rounded-2xl bg-gradient-to-b from-white/[0.04] to-white/[0.01] border border-white/[0.07] hover:border-primary/40 transition-all duration-500 hover:shadow-[0_0_50px_oklch(0.648_0.2_131.684/0.12)] overflow-hidden backdrop-blur-sm cursor-default">
                        <!-- Card inner glow on hover -->
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 bg-gradient-to-br from-primary/[0.06] via-transparent to-transparent"></div>
                        <!-- Top border highlight -->
                        <div class="absolute top-0 left-4 right-4 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:via-primary/50 transition-all duration-500"></div>
                        <div class="relative z-10">
                            <div
                                class="w-14 h-14 rounded-xl flex items-center justify-center mb-6 bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/20 group-hover:from-primary/30 group-hover:to-primary/10 group-hover:border-primary/40 transition-all duration-300 shadow-[0_0_20px_oklch(0.648_0.2_131.684/0.1)] group-hover:shadow-[0_0_30px_oklch(0.648_0.2_131.684/0.3)]">
                                <component :is="feature.icon" :size="26" class="text-primary" />
                            </div>
                            <h3 class="font-dm-sans text-xl font-semibold mb-3 group-hover:text-white transition-colors">{{ feature.title }}</h3>
                            <p class="text-[#71717A] leading-relaxed group-hover:text-[#8A8A92] transition-colors">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="relative py-32 px-4 overflow-hidden">
            <!-- Background accent -->
            <div
                class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[1000px] h-[600px] bg-gradient-to-r from-primary/8 via-[oklch(0.70_0.22_128)]/6 to-primary/8 rounded-full blur-[140px]">
            </div>

            <div class="max-w-6xl mx-auto relative">
                <div class="text-center mb-20">
                    <span class="inline-block text-primary text-xs font-bold tracking-[0.25em] uppercase mb-4 px-3 py-1 rounded-full border border-primary/30 bg-primary/10">How It Works</span>
                    <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6 leading-tight">
                        Get Started in
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-[oklch(0.78_0.22_110)] to-[oklch(0.72_0.24_155)]">
                            3 Simple Steps</span>
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="(step, index) in steps" :key="step.number" class="relative text-center group">
                        <!-- Connector line -->
                        <div v-if="index < steps.length - 1"
                            class="hidden md:block absolute top-12 left-[60%] w-[80%] h-px bg-gradient-to-r from-primary/60 via-primary/20 to-transparent">
                        </div>

                        <div
                            class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 relative border border-primary/30 bg-gradient-to-br from-primary/15 to-primary/5 shadow-[0_0_40px_oklch(0.648_0.2_131.684/0.2)] group-hover:shadow-[0_0_60px_oklch(0.648_0.2_131.684/0.4)] transition-all duration-500">
                            <!-- Inner ring -->
                            <div class="absolute inset-2 rounded-full border border-primary/20"></div>
                            <span class="text-3xl font-bold bg-gradient-to-br from-primary to-[oklch(0.72_0.24_155)] bg-clip-text text-transparent">{{ step.number }}</span>
                        </div>
                        <h3 class="font-dm-sans text-xl font-semibold mb-3">{{ step.title }}</h3>
                        <p class="text-[#71717A]">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="relative py-20 px-4">
            <div class="max-w-6xl mx-auto">
                <div
                    class="relative grid grid-cols-2 md:grid-cols-4 gap-0 rounded-3xl overflow-hidden border border-white/[0.08] backdrop-blur-sm">
                    <!-- Background gradient -->
                    <div class="absolute inset-0 bg-gradient-to-br from-white/[0.04] via-primary/[0.03] to-white/[0.02]"></div>
                    <!-- Top border glow -->
                    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/50 to-transparent"></div>
                    <!-- Bottom border glow -->
                    <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>

                    <div v-for="(stat, i) in [
                        { value: '10K+', label: 'Active Users' },
                        { value: '$2M+', label: 'Tracked Monthly' },
                        { value: '50K+', label: 'Goals Achieved' },
                        { value: '4.9', label: 'User Rating' },
                    ]" :key="i" class="relative text-center py-10 px-6 group">
                        <!-- Vertical dividers -->
                        <div v-if="i > 0" class="absolute left-0 top-4 bottom-4 w-px bg-white/[0.07]"></div>
                        <div
                            class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-br from-primary via-[oklch(0.78_0.22_115)] to-[oklch(0.70_0.22_128)] mb-2 group-hover:drop-shadow-[0_0_20px_oklch(0.648_0.2_131.684/0.6)] transition-all duration-300">
                            {{ stat.value }}
                        </div>
                        <p class="text-[#71717A] text-sm font-medium">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="relative py-32 px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-20">
                    <span
                        class="inline-block text-primary text-xs font-bold tracking-[0.25em] uppercase mb-4 px-3 py-1 rounded-full border border-primary/30 bg-primary/10">Testimonials</span>
                    <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6 leading-tight">
                        Loved by
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-[oklch(0.78_0.22_110)] to-[oklch(0.72_0.24_155)]">Thousands</span>
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div v-for="testimonial in testimonials" :key="testimonial.name"
                        class="group relative p-8 rounded-2xl bg-gradient-to-b from-white/[0.05] to-white/[0.01] border border-white/[0.07] hover:border-primary/30 transition-all duration-500 hover:shadow-[0_0_40px_oklch(0.648_0.2_131.684/0.1)] overflow-hidden backdrop-blur-sm">
                        <!-- Top border highlight -->
                        <div class="absolute top-0 left-4 right-4 h-px bg-gradient-to-r from-transparent via-white/15 to-transparent group-hover:via-primary/40 transition-all duration-500"></div>
                        <!-- Quote mark -->
                        <div class="absolute top-4 right-6 text-6xl font-serif text-primary/10 group-hover:text-primary/20 transition-colors leading-none select-none">"</div>
                        <div class="flex items-center gap-1 mb-5">
                            <Star v-for="i in testimonial.rating" :key="i" :size="14"
                                class="fill-[#FFD700] text-[#FFD700]" />
                        </div>
                        <p class="text-[#C8C8D0] mb-7 leading-relaxed text-sm">"{{ testimonial.content }}"</p>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-11 h-11 rounded-full bg-gradient-to-br from-primary via-[oklch(0.70_0.22_128)] to-[oklch(0.65_0.24_155)] flex items-center justify-center text-black font-bold text-sm shadow-[0_0_20px_oklch(0.648_0.2_131.684/0.4)]">
                                {{ testimonial.avatar }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-sm">{{ testimonial.name }}</h4>
                                <p class="text-xs text-[#71717A]">{{ testimonial.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faqs" class="relative py-32 px-4">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-20">
                    <span class="inline-block text-primary text-xs font-bold tracking-[0.25em] uppercase mb-4 px-3 py-1 rounded-full border border-primary/30 bg-primary/10">FAQs</span>
                    <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6 leading-tight">
                        Frequently Asked
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-[oklch(0.78_0.22_110)] to-[oklch(0.72_0.24_155)]">Questions</span>
                    </h2>
                </div>

                <div class="space-y-3">
                    <div v-for="(faq, index) in faqs" :key="index"
                        class="rounded-2xl border overflow-hidden transition-all duration-300"
                        :class="faq.open ? 'bg-gradient-to-b from-white/[0.05] to-white/[0.02] border-primary/30 shadow-[0_0_30px_oklch(0.648_0.2_131.684/0.08)]' : 'bg-white/[0.02] border-white/[0.07] hover:border-white/[0.14]'">
                        <button @click="toggleFaq(index)"
                            class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer">
                            <span class="font-semibold text-base pr-4" :class="faq.open ? 'text-white' : 'text-[#D5D5D5]'">{{ faq.question }}</span>
                            <div class="flex-none w-7 h-7 rounded-full flex items-center justify-center transition-all duration-300"
                                :class="faq.open ? 'bg-primary/20 border border-primary/40' : 'bg-white/5 border border-white/10'">
                                <ChevronRight :size="14" class="transition-transform duration-300 text-primary"
                                    :class="faq.open ? 'rotate-90' : ''" />
                            </div>
                        </button>
                        <div class="overflow-hidden transition-all duration-300"
                            :class="faq.open ? 'max-h-40 pb-6 px-6' : 'max-h-0'">
                            <p class="text-[#9A9AA8] leading-relaxed">{{ faq.answer }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-24 px-4">
            <div class="max-w-4xl mx-auto">
                <div
                    class="relative rounded-3xl overflow-hidden p-12 md:p-16 text-center border border-white/[0.08]">
                    <!-- Background layers -->
                    <div class="absolute inset-0 bg-gradient-to-br from-[#13121C] via-[#0F0E18] to-[#0D0C15]"></div>
                    <!-- Gradient orbs -->
                    <div class="absolute top-0 left-1/4 w-72 h-72 bg-primary/25 rounded-full blur-[100px]"></div>
                    <div class="absolute bottom-0 right-1/4 w-72 h-72 bg-[oklch(0.70_0.22_128)]/20 rounded-full blur-[100px]"></div>
                    <!-- Top border glow -->
                    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/60 to-transparent"></div>
                    <!-- Corner accents -->
                    <div class="absolute top-0 left-0 w-32 h-32 bg-gradient-to-br from-primary/10 to-transparent rounded-br-full blur-2xl"></div>
                    <div class="absolute bottom-0 right-0 w-32 h-32 bg-gradient-to-tl from-[oklch(0.70_0.22_128)]/10 to-transparent rounded-tl-full blur-2xl"></div>

                    <div class="relative z-10">
                        <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6 leading-tight">
                            Ready to Transform Your
                            <br />
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-[oklch(0.78_0.22_110)] to-[oklch(0.72_0.24_155)]">Financial
                                Life?</span>
                        </h2>
                        <p class="text-[#9A9AA8] text-lg mb-10 max-w-2xl mx-auto">
                            Join thousands of users who have already taken control of their finances.
                            Start your journey today — it's free!
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                            <Link :href="route('register')">
                                <button
                                    class="group relative px-8 py-4 rounded-full font-bold tracking-wide font-dm-sans text-black cursor-pointer transition-all duration-300 hover:scale-105 flex items-center gap-2 overflow-hidden bg-gradient-to-r from-primary via-[oklch(0.70_0.22_128)] to-primary bg-size-200 hover:bg-right shadow-[0_4px_30px_oklch(0.648_0.2_131.684/0.5)] hover:shadow-[0_4px_60px_oklch(0.648_0.2_131.684/0.8)]">
                                    Get Started for Free
                                    <ArrowRight :size="20" class="group-hover:translate-x-1 transition-transform" />
                                </button>
                            </Link>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-6 mt-8 text-sm text-[#71717A]">
                            <span class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full bg-primary/20 border border-primary/40 flex items-center justify-center">
                                    <Check :size="10" class="text-primary" />
                                </div>
                                No credit card required
                            </span>
                            <span class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full bg-primary/20 border border-primary/40 flex items-center justify-center">
                                    <Check :size="10" class="text-primary" />
                                </div>
                                Free forever plan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative py-10 px-4 border-t border-white/[0.06]">
            <!-- Top glow -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 h-px w-3/4 bg-gradient-to-r from-transparent via-primary/20 to-transparent"></div>
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo.svg" alt="Nabungo Logo" class="h-6" />
                    </div>
                    <p class="text-[#52525B] text-sm">
                        © {{ new Date().getFullYear() }} Nabungo. All rights reserved.
                    </p>
                    <div class="flex items-center gap-6 text-sm text-[#52525B]">
                        <a href="#" class="hover:text-primary transition-colors duration-200">Privacy</a>
                        <a href="#" class="hover:text-primary transition-colors duration-200">Terms</a>
                        <a href="#" class="hover:text-primary transition-colors duration-200">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(16px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in-delay {
    0%, 30% { opacity: 0; transform: translateY(16px); }
    100% { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fade-in 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.bg-size-200 {
    background-size: 200% 100%;
}

.hover\:bg-right:hover {
    background-position: right center;
}
</style>
