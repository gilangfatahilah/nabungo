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
        <div class="fixed inset-0 pointer-events-none">
            <div
                class="aspect-square bg-white rounded-full blur-[90px] flex-none h-[930px] w-[930px] absolute -left-[395px] -top-[490px] opacity-[0.01]">
            </div>
            <div
                class="aspect-square bg-[#00FFB2] rounded-full blur-[120px] flex-none h-[694px] w-[694px] absolute -left-[347px] -top-[362px] opacity-[0.07]">
            </div>
            <div
                class="aspect-square bg-[#00FFB2] rounded-full blur-[120px] flex-none h-[694px] w-[694px] absolute -right-[504px] top-[97px] opacity-[0.05]">
            </div>
        </div>

        <Navbar />

        <!-- Hero Section -->
        <section id="hero" class="relative min-h-screen flex flex-col items-center justify-center px-4 py-20">
            <div class="max-w-5xl mx-auto text-center z-10">
                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#00FFB2]/10 border border-[#00FFB2]/20 mb-8 animate-fade-in">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#00FFB2] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#00FFB2]"></span>
                    </span>
                    <span class="text-sm text-[#00FFB2] font-medium">Your Personal Finance Companion</span>
                </div>

                <!-- Main Heading -->
                <h1 class="font-dm-sans text-5xl md:text-7xl font-bold tracking-tight leading-tight mb-6">
                    Take Control of Your
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF]">
                        Financial Future
                    </span>
                </h1>

                <p class="font-dm-sans text-[#A1A1AA] text-lg md:text-xl max-w-2xl mx-auto mb-10">
                    Nabungo helps you track expenses, set budgets, achieve savings goals, and gain
                    complete control over your money with beautiful insights and smart analytics.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                    <Link :href="route('register')">
                        <button
                            class="group px-8 py-4 rounded-full bg-[#00FFB2] font-bold tracking-wide font-dm-sans text-black cursor-pointer transition-all duration-300 hover:shadow-[0_0_40px_rgba(0,255,178,0.3)] hover:scale-105 flex items-center gap-2">
                            Start Free Today
                            <ArrowRight :size="20" class="group-hover:translate-x-1 transition-transform" />
                        </button>
                    </Link>
                    <a href="#features">
                        <button
                            class="px-8 py-4 rounded-full bg-transparent border border-[#333] font-medium font-dm-sans text-white cursor-pointer transition-all duration-300 hover:border-[#00FFB2]/50 hover:bg-[#00FFB2]/5">
                            Explore Features
                        </button>
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="flex flex-col items-center gap-4">
                    <div class="flex items-center gap-1">
                        <Star v-for="i in 5" :key="i" :size="18" class="fill-[#FFD700] text-[#FFD700]" />
                    </div>
                    <p class="text-sm text-[#71717A]">
                        Trusted by <span class="text-white font-semibold">10,000+</span> users to
                        manage their finances
                    </p>
                </div>
            </div>

            <!-- Dashboard Preview -->
            <div class="relative w-full max-w-6xl mx-auto mt-16 px-4">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl shadow-black/50">
                    <!-- Glow effect -->
                    <div
                        class="absolute -inset-1 bg-gradient-to-r from-[#00FFB2]/20 via-transparent to-[#00D9FF]/20 rounded-2xl blur-xl">
                    </div>
                    <div class="relative rounded-2xl overflow-hidden border border-white/10">
                        <img src="/images/dashboard.png" alt="Nabungo Dashboard" class="w-full h-auto" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#08070E] via-transparent to-transparent">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="relative py-24 px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <span
                        class="text-[#00FFB2] text-sm font-semibold tracking-wider uppercase mb-4 block">Features</span>
                    <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6">
                        Everything You Need to
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF]">Master
                            Your Money</span>
                    </h2>
                    <p class="text-[#A1A1AA] text-lg max-w-2xl mx-auto">
                        Powerful features designed to simplify your financial life and help you
                        achieve your goals faster.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="feature in features" :key="feature.title"
                        class="group p-8 rounded-2xl bg-gradient-to-b from-[#12111A] to-[#0D0C14] border border-white/5 hover:border-[#00FFB2]/30 transition-all duration-500 hover:shadow-[0_0_40px_rgba(0,255,178,0.1)]">
                        <div
                            class="w-14 h-14 rounded-xl bg-[#00FFB2]/10 flex items-center justify-center mb-6 group-hover:bg-[#00FFB2]/20 transition-colors">
                            <component :is="feature.icon" :size="28" class="text-[#00FFB2]" />
                        </div>
                        <h3 class="font-dm-sans text-xl font-semibold mb-3">{{ feature.title }}</h3>
                        <p class="text-[#71717A] leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="relative py-24 px-4 overflow-hidden">
            <!-- Background accent -->
            <div
                class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-[#00FFB2]/5 rounded-full blur-[120px]">
            </div>

            <div class="max-w-6xl mx-auto relative">
                <div class="text-center mb-16">
                    <span class="text-[#00FFB2] text-sm font-semibold tracking-wider uppercase mb-4 block">How It
                        Works</span>
                    <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6">
                        Get Started in
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF]">3
                            Simple Steps</span>
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="(step, index) in steps" :key="step.number" class="relative text-center">
                        <!-- Connector line -->
                        <div v-if="index < steps.length - 1"
                            class="hidden md:block absolute top-12 left-[60%] w-[80%] h-[2px] bg-gradient-to-r from-[#00FFB2]/50 to-transparent">
                        </div>

                        <div
                            class="w-24 h-24 rounded-full bg-gradient-to-br from-[#00FFB2]/20 to-[#00FFB2]/5 flex items-center justify-center mx-auto mb-6 border border-[#00FFB2]/30">
                            <span class="text-3xl font-bold text-[#00FFB2]">{{ step.number }}</span>
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
                    class="grid grid-cols-2 md:grid-cols-4 gap-8 p-8 md:p-12 rounded-3xl bg-gradient-to-r from-[#12111A] via-[#0F0E16] to-[#12111A] border border-white/5">
                    <div class="text-center">
                        <div
                            class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF] mb-2">
                            10K+
                        </div>
                        <p class="text-[#71717A]">Active Users</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF] mb-2">
                            $2M+
                        </div>
                        <p class="text-[#71717A]">Tracked Monthly</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF] mb-2">
                            50K+
                        </div>
                        <p class="text-[#71717A]">Goals Achieved</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF] mb-2">
                            4.9
                        </div>
                        <p class="text-[#71717A]">User Rating</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="relative py-24 px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <span
                        class="text-[#00FFB2] text-sm font-semibold tracking-wider uppercase mb-4 block">Testimonials</span>
                    <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6">
                        Loved by
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF]">Thousands</span>
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div v-for="testimonial in testimonials" :key="testimonial.name"
                        class="p-8 rounded-2xl bg-gradient-to-b from-[#12111A] to-[#0D0C14] border border-white/5 hover:border-[#00FFB2]/20 transition-all duration-300">
                        <div class="flex items-center gap-1 mb-6">
                            <Star v-for="i in testimonial.rating" :key="i" :size="16"
                                class="fill-[#FFD700] text-[#FFD700]" />
                        </div>
                        <p class="text-[#D5D5D5] mb-6 leading-relaxed">"{{ testimonial.content }}"</p>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-[#00FFB2] to-[#00D9FF] flex items-center justify-center text-black font-bold">
                                {{ testimonial.avatar }}
                            </div>
                            <div>
                                <h4 class="font-semibold">{{ testimonial.name }}</h4>
                                <p class="text-sm text-[#71717A]">{{ testimonial.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faqs" class="relative py-24 px-4">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-16">
                    <span class="text-[#00FFB2] text-sm font-semibold tracking-wider uppercase mb-4 block">FAQs</span>
                    <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6">
                        Frequently Asked
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF]">Questions</span>
                    </h2>
                </div>

                <div class="space-y-4">
                    <div v-for="(faq, index) in faqs" :key="index"
                        class="rounded-xl border border-white/10 overflow-hidden transition-all duration-300"
                        :class="faq.open ? 'bg-[#12111A]' : 'bg-transparent'">
                        <button @click="toggleFaq(index)"
                            class="w-full px-6 py-5 flex items-center justify-between text-left cursor-pointer">
                            <span class="font-semibold text-lg">{{ faq.question }}</span>
                            <ChevronRight :size="20" class="text-[#00FFB2] transition-transform duration-300"
                                :class="faq.open ? 'rotate-90' : ''" />
                        </button>
                        <div class="overflow-hidden transition-all duration-300"
                            :class="faq.open ? 'max-h-40 pb-5 px-6' : 'max-h-0'">
                            <p class="text-[#A1A1AA]">{{ faq.answer }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-24 px-4">
            <div class="max-w-4xl mx-auto">
                <div
                    class="relative rounded-3xl overflow-hidden p-12 md:p-16 text-center bg-gradient-to-br from-[#12111A] to-[#0D0C14] border border-white/10">
                    <!-- Glow effects -->
                    <div class="absolute top-0 left-1/4 w-64 h-64 bg-[#00FFB2]/20 rounded-full blur-[100px]"></div>
                    <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-[#00D9FF]/20 rounded-full blur-[100px]"></div>

                    <div class="relative z-10">
                        <h2 class="font-dm-sans text-4xl md:text-5xl font-bold mb-6">
                            Ready to Transform Your
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-[#00FFB2] to-[#00D9FF]">Financial
                                Life?</span>
                        </h2>
                        <p class="text-[#A1A1AA] text-lg mb-10 max-w-2xl mx-auto">
                            Join thousands of users who have already taken control of their finances.
                            Start your journey today — it's free!
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                            <Link :href="route('register')">
                                <button
                                    class="group px-8 py-4 rounded-full bg-[#00FFB2] font-bold tracking-wide font-dm-sans text-black cursor-pointer transition-all duration-300 hover:shadow-[0_0_40px_rgba(0,255,178,0.3)] hover:scale-105 flex items-center gap-2">
                                    Get Started for Free
                                    <ArrowRight :size="20" class="group-hover:translate-x-1 transition-transform" />
                                </button>
                            </Link>
                        </div>
                        <div class="flex items-center justify-center gap-6 mt-8 text-sm text-[#71717A]">
                            <span class="flex items-center gap-2">
                                <Check :size="16" class="text-[#00FFB2]" />
                                No credit card required
                            </span>
                            <span class="flex items-center gap-2">
                                <Check :size="16" class="text-[#00FFB2]" />
                                Free forever plan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative py-12 px-4 border-t border-white/5">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo2.svg" alt="Nabungo Logo" class="h-6" />
                    </div>
                    <p class="text-[#71717A] text-sm">
                        © {{ new Date().getFullYear() }} Nabungo. All rights reserved.
                    </p>
                    <div class="flex items-center gap-6 text-sm text-[#71717A]">
                        <a href="#" class="hover:text-[#00FFB2] transition-colors">Privacy</a>
                        <a href="#" class="hover:text-[#00FFB2] transition-colors">Terms</a>
                        <a href="#" class="hover:text-[#00FFB2] transition-colors">Contact</a>
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
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}
</style>
