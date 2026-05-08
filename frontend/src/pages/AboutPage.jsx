import { Gift, Zap, Star, Shield, Users, ArrowRight } from 'lucide-react'
import { Link } from 'react-router-dom'

const STATS = [
  { value: '500+', label: 'Brand Partners' },
  { value: '2%', label: 'Max Rewards Back' },
  { value: '₹1', label: 'Per PayFlex Point' },
  { value: '24/7', label: 'Instant Delivery' },
]

const VALUES = [
  {
    Icon: Zap,
    color: 'bg-amber-50 text-amber-500',
    title: 'Instant Rewards',
    body: 'Earn PayFlex Points automatically the moment your payment goes through — no waiting, no claiming.',
  },
  {
    Icon: Shield,
    color: 'bg-blue-50 text-blue-500',
    title: 'Secure Payments',
    body: 'Every transaction is encrypted and processed through certified payment gateways. Your data stays yours.',
  },
  {
    Icon: Star,
    color: 'bg-purple-50 text-purple-500',
    title: 'Premium Brands',
    body: 'Access gift vouchers and services from nearly every major brand in India, all in one platform.',
  },
  {
    Icon: Users,
    color: 'bg-green-50 text-green-500',
    title: 'For Everyone',
    body: "Whether you're an individual, gifting a friend, or running corporate bulk orders — PayFlex scales with you.",
  },
]

export default function AboutPage() {
  return (
    <div className="min-h-screen">
      {/* Hero */}
      <section className="relative bg-gradient-to-br from-primary-600 via-primary-500 to-orange-500 text-white overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          {Array.from({ length: 20 }).map((_, i) => (
            <div
              key={i}
              className="absolute rounded-full bg-white"
              style={{
                width: Math.random() * 80 + 20,
                height: Math.random() * 80 + 20,
                top: `${Math.random() * 100}%`,
                left: `${Math.random() * 100}%`,
                opacity: Math.random() * 0.5,
              }}
            />
          ))}
        </div>
        <div className="relative max-w-4xl mx-auto px-6 py-24 text-center">
          <div className="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
            <Gift size={13} /> India's Smart Payments & Rewards Platform
          </div>
          <h1 className="text-4xl sm:text-5xl font-extrabold leading-tight mb-6">
            Every Transaction,
            <br />A Reward Waiting for You
          </h1>
          <p className="text-lg text-white/85 max-w-2xl mx-auto leading-relaxed">
            PayFlex is built on one simple idea: your money should work harder for you. We combine
            seamless digital payments with an instant loyalty programme so every rupee you spend
            earns you more.
          </p>
        </div>
      </section>

      {/* Stats */}
      <section className="bg-white border-b border-gray-100">
        <div className="max-w-5xl mx-auto px-6 py-12 grid grid-cols-2 sm:grid-cols-4 gap-8">
          {STATS.map(({ value, label }) => (
            <div key={label} className="text-center">
              <p className="text-3xl font-extrabold text-primary-600">{value}</p>
              <p className="text-sm text-gray-500 mt-1">{label}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Our story */}
      <section className="max-w-4xl mx-auto px-6 py-20">
        <div className="grid md:grid-cols-2 gap-14 items-center">
          <div>
            <span className="text-xs font-bold uppercase tracking-widest text-primary-500 mb-3 block">
              Our Story
            </span>
            <h2 className="text-3xl font-extrabold text-gray-900 mb-5 leading-tight">
              Redefining loyalty & digital payments in India
            </h2>
            <div className="space-y-4 text-gray-600 text-sm leading-relaxed">
              <p>
                PayFlex is India's smart payments and rewards platform, designed to make every
                transaction seamless, rewarding, and convenient. We empower our customers to earn
                and redeem PayFlex Points while transacting with leading brands across the country,
                turning every payment into an opportunity to save and enjoy exclusive benefits.
              </p>
              <p>
                We work with nearly all major brands in India, enabling customers to buy gift
                vouchers, pay for services, and shop with ease. With PayFlex, every transaction adds
                value — customers earn loyalty points instantly, which can be redeemed for
                discounts, special offers, or future purchases.
              </p>
              <p>
                Our mission is to simplify digital payments while providing maximum rewards to our
                users. From daily essentials to lifestyle products, corporate gifting, and special
                occasions, PayFlex ensures that every purchase is secure, fast, and rewarding.
              </p>
            </div>
          </div>

          <div className="bg-gradient-to-br from-primary-50 to-orange-50 rounded-3xl p-8 border border-primary-100">
            <div className="flex items-center gap-3 mb-6">
              <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-orange-500 flex items-center justify-center shadow">
                <Gift size={20} className="text-white" />
              </div>
              <div>
                <p className="font-bold text-gray-900">PayFlex Points</p>
                <p className="text-xs text-gray-500">1 Point = ₹1</p>
              </div>
            </div>
            <p className="text-sm text-gray-600 leading-relaxed mb-5">
              By combining innovative technology with strategic partnerships, PayFlex is redefining
              loyalty and digital payments in India. We are committed to helping our users shop
              smarter, earn effortlessly, and enjoy the benefits of a truly flexible payment
              ecosystem.
            </p>
            <p className="text-sm font-semibold text-primary-600 italic">
              "With PayFlex, every transaction works harder for you."
            </p>

            <div className="mt-6 pt-5 border-t border-primary-100 grid grid-cols-2 gap-3 text-xs">
              {[
                ['Earn up to', '2% back'],
                ['Points value', '₹1 each'],
                ['Valid for', '30 days'],
                ['Payment modes', 'All major'],
              ].map(([k, v]) => (
                <div key={k}>
                  <p className="text-gray-400">{k}</p>
                  <p className="font-bold text-gray-800">{v}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Values */}
      <section className="bg-gray-50 py-20">
        <div className="max-w-5xl mx-auto px-6">
          <div className="text-center mb-12">
            <span className="text-xs font-bold uppercase tracking-widest text-primary-500 mb-3 block">
              Why PayFlex
            </span>
            <h2 className="text-3xl font-extrabold text-gray-900">Built around your benefit</h2>
          </div>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {VALUES.map(({ Icon, color, title, body }) => (
              <div
                key={title}
                className="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow"
              >
                <div
                  className={`w-10 h-10 rounded-xl flex items-center justify-center mb-4 ${color}`}
                >
                  <Icon size={20} />
                </div>
                <h3 className="font-bold text-gray-800 mb-2">{title}</h3>
                <p className="text-xs text-gray-500 leading-relaxed">{body}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Contact / CTA */}
      <section className="max-w-3xl mx-auto px-6 py-20 text-center">
        <h2 className="text-3xl font-extrabold text-gray-900 mb-4">Get in touch</h2>
        <p className="text-gray-500 text-sm mb-8">
          Have a question, partnership inquiry, or need support? We're here to help.
        </p>
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-left max-w-md mx-auto space-y-4 text-sm">
          <div>
            <span className="text-gray-400 block text-xs mb-0.5">Company</span>
            <span className="font-semibold text-gray-800">SmartPayflex Payments Pvt. Ltd.</span>
          </div>
          <div>
            <span className="text-gray-400 block text-xs mb-0.5">Email</span>
            <a
              href="mailto:info@payflex.in"
              className="text-primary-600 font-medium hover:underline"
            >
              info@payflex.in
            </a>
          </div>
          <div>
            <span className="text-gray-400 block text-xs mb-0.5">Address</span>
            <span className="text-gray-700">
              Unit No. 607, 6th Floor, Capital Business Park, Sector-48, Sohna Road, Gurgaon,
              Haryana — 122018
            </span>
          </div>
        </div>
        <Link to="/gift-cards" className="inline-flex items-center gap-2 btn-primary mt-10">
          Explore Gift Cards <ArrowRight size={15} />
        </Link>
      </section>
    </div>
  )
}
