import { Shield } from 'lucide-react'

const SECTIONS = [
  {
    title: '1. Scope & Applicability',
    body: `This Policy applies to all personal data processed by Payflex in the course of providing Services, including:
• Individuals who use our Services.
• Merchants, partners, customers, and other persons whose data we process for business operations.
When we refer to "you" or "your", it means the individual whose personal data is processed under this Policy.`,
  },
  {
    title: '2. What Information Do We Collect?',
    body: `We may collect and process the following categories of personal data (as applicable and necessary):
• Identity & Contact Information: Name, email, phone, postal address, etc.
• Transaction & Payment Information: Transaction IDs, order details, payment instrument details (cards/UPI processed by third parties).
• Usage & Technical Data: Cookies, IP addresses, device information, browser information, analytics data.
• Support & Correspondence: Emails, calls, chats, customer service logs.
• Other Information You Provide: Feedback, preferences, or other details you give voluntarily.`,
  },
  {
    title: '3. Purposes of Processing',
    body: `We process personal data for one or more of the following purposes:
• To provide and operate our Services, including payment processing, notifications, reconciliation, and support.
• To send transactional communications regarding orders, payments, refunds, alerts, and updates.
• To undertake fraud detection and prevention, and comply with tax, accounting, and regulatory obligations.
• To provide customer support, respond to queries, complaints, and disputes.
• To send marketing/promotional communications where lawful and with consent.
• To detect, investigate, and prevent security incidents or fraudulent activity.`,
  },
  {
    title: '4. Lawful Basis for Processing',
    body: `Where applicable, Payflex will rely on one or more lawful bases to process personal data, including:
• Performance of a Contract: Processing necessary to fulfil contractual obligations.
• Compliance with Legal Obligations: Processing necessary to comply with applicable law.
• Consent: Where explicit consent has been obtained (e.g., marketing).
• Legitimate Interests: For fraud detection, security, and internal analytics, provided rights are not overridden.`,
  },
  {
    title: '5. How We Share Personal Data',
    body: `We may disclose personal data to the following categories of recipients as necessary:
• Payment processors and banking partners (for payments and refunds).
• Communication providers (SMS, email, push notifications).
• IT service providers, cloud hosting, and analytics providers.
• CRM and customer support providers.
• Professional advisers, auditors, legal and regulatory authorities.
• Successors and assigns in case of business transfer or reorganisation.
All recipients are required to process personal data only for the specified purposes and under confidentiality and security arrangements.`,
  },
  {
    title: '6. Confidentiality & Client Records',
    body: 'We treat client and user records as confidential and will not disclose such records, other than to suppliers or as required by law. You may request access to or copies of your personal records by contacting our support team.',
  },
  {
    title: '7. Payment & Third-Party Processors',
    body: 'Payments on our Website may be processed via third-party payment processors. We do not control their privacy practices; please review their respective privacy policies.',
  },
  {
    title: '8. Cookies & Similar Technologies',
    body: 'We may use cookies, web beacons, and similar technologies to collect usage data and improve our Services. You may disable cookies via your browser settings; however, this may affect functionality.',
  },
  {
    title: '9. Data Retention',
    body: 'We retain personal data only as long as necessary to fulfil the purposes in this Policy, comply with legal obligations, resolve disputes, and enforce agreements. After expiry of retention periods, personal data will be anonymised, deleted, or aggregated securely.',
  },
  {
    title: '10. Data Subject Rights',
    body: `Subject to applicable law, you have the right to:
• Request access to your personal data.
• Request correction of inaccurate or incomplete data.
• Request erasure of personal data, subject to legal obligations.
• Withdraw consent where processing is based on consent.
• Object to or restrict processing where applicable.
• Request data portability.
To exercise these rights, contact us at info@payflex.in.`,
  },
  {
    title: '11. Security Measures',
    body: 'We implement reasonable technical and organisational measures to protect personal data against unauthorised access, loss, misuse, alteration, or destruction. However, no system is completely secure.',
  },
  {
    title: '12. Cross-Border Transfers',
    body: 'Where personal data is transferred outside India (e.g., to cloud hosting providers), such transfers will comply with applicable laws and include appropriate safeguards to protect the data.',
  },
  {
    title: '13. Children & Age Restrictions',
    body: 'Our services are intended for persons aged 18 years or above. We do not knowingly collect personal data from children under 18. If discovered, such data will be deleted in accordance with the law.',
  },
  {
    title: '14. Marketing & Promotional Communications',
    body: 'We may use personal data to send marketing communications if we have a lawful basis to do so (including consent). Users can opt out of marketing messages by following unsubscribe instructions or contacting support.',
  },
  {
    title: '15. Your Consent',
    body: 'By using the Website and providing your information, you consent explicitly to the collection, use, and sharing of your information as outlined in this Privacy Policy. If we update or change our Privacy Policy, those changes will be posted on this page. Please regularly review our Privacy Policy to stay informed.',
  },
  {
    title: '16. Contact Information',
    body: `For questions, concerns, or complaints regarding this Policy or our data practices, reach out to:
SmartPayflex Payments Pvt. Ltd.
Email: info@payflex.in
Address: Unit No. 607, 6th Floor, Capital Business Park, Sector-48, Sohna Road, Gurgaon, Haryana, India — 122018`,
  },
]

export default function PrivacyPolicyPage() {
  return (
    <PolicyLayout
      Icon={Shield}
      iconBg="bg-blue-500"
      tag="Legal"
      title="Privacy Policy"
      effectiveDate="Effective Date: 01 February 2026"
      intro={`SmartPayflex Payments Pvt. Ltd. ("Payflex", "we", "us", or "the Company") is committed to protecting the privacy of individuals whose personal data we process in connection with our website (https://payflex.in) and payment services ("Services"). This Privacy Policy explains how we collect, use, disclose, transfer, secure, retain, and manage personal data in compliance with applicable data protection and information technology laws in India, including the Digital Personal Data Protection Act, 2023 and the Information Technology Act, 2000.`}
      sections={SECTIONS}
    />
  )
}

function PolicyLayout({ Icon, iconBg, tag, title, effectiveDate, intro, sections }) {
  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-100">
        <div className="max-w-3xl mx-auto px-6 py-12">
          <div className="flex items-center gap-3 mb-4">
            <div className={`w-10 h-10 rounded-xl ${iconBg} flex items-center justify-center`}>
              <Icon size={20} className="text-white" />
            </div>
            <span className="text-xs font-bold uppercase tracking-widest text-gray-400">{tag}</span>
          </div>
          <h1 className="text-3xl font-extrabold text-gray-900 mb-2">{title}</h1>
          {effectiveDate && <p className="text-xs text-gray-400">{effectiveDate}</p>}
          {intro && <p className="text-sm text-gray-600 leading-relaxed mt-4">{intro}</p>}
        </div>
      </div>

      {/* Content */}
      <div className="max-w-3xl mx-auto px-6 py-12 space-y-8">
        {sections.map(({ title: sTitle, body }) => (
          <section
            key={sTitle}
            className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
          >
            <h2 className="text-base font-bold text-gray-800 mb-3">{sTitle}</h2>
            <div className="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{body}</div>
          </section>
        ))}
      </div>
    </div>
  )
}

export { PolicyLayout }
