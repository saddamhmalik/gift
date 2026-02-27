import { FileText } from 'lucide-react'
import { PolicyLayout } from './PrivacyPolicyPage'

const SECTIONS = [
  {
    title: 'Introduction',
    body: `By accessing this Website, you are deemed to have read and agreed to the following terms and conditions. The following terminology applies to these Terms and Conditions, Privacy Statement, and Disclaimer Notice and any or all Agreements:

"Client", "You" and "Your" refers to you, the person accessing this Website and accepting the Company's terms and conditions.
"The Company", "Ourselves", "We" and "Us" refers to SmartPayflex Payments Pvt. Ltd.
"Party", "Parties", or "Us" refers to both the Client and ourselves, or either the Client or ourselves.

All terms refer to the offer, acceptance and consideration of payment necessary to undertake the process of our assistance to the Client in the most appropriate manner, in accordance with and subject to prevailing Indian Law.`,
  },
  {
    title: 'Privacy Statement',
    body: 'We are committed to protecting your privacy. Only authorized personnel, on a strict need-to-know basis, may use any information collected from individual customers, and handled in compliance with applicable data protection laws. We constantly review our systems and data to ensure the best possible service to our customers.',
  },
  {
    title: 'Confidentiality',
    body: `Client records are regarded as confidential and will not be divulged to any third party, other than our manufacturer/supplier(s) and if legally required to do so, to the appropriate authorities. Disclosure is permitted only:

A. Where disclosure is necessary for the performance of our services (e.g., to our suppliers or service partners).
B. Where the Client has given explicit consent.
C. Where required by law or regulatory authority.

Clients may request access to their records subject to providing reasonable notice. We reserve the right to verify the identity of the requester.`,
  },
  {
    title: 'Disclaimer — Exclusions and Limitations',
    body: `The information on this Website is provided on an "as is" basis. To the fullest extent permitted by law, the Company:

• Disclaims all representations and warranties, whether express or implied, relating to this Website, including warranties of accuracy, completeness, merchantability, or fitness for a particular purpose.
• Excludes all liability for any loss or damage, whether direct, indirect, incidental, consequential, or special, arising from your use of or inability to use the Website. This includes loss of business, revenue, profits, anticipated savings, goodwill, data, or damage to hardware or software.

This Company does not exclude liability for death or personal injury caused by its negligence. None of your statutory rights as a consumer are affected.`,
  },
  {
    title: 'Payment',
    body: `Payment to www.payflex.in will be processed by authorised payment gateways, which may include:
• Credit Cards
• Debit Cards
• UPI
• Wallets
• Net Banking

Payments for services or products offered on the Website must be made in full upfront. Access to services, products, or vouchers will only be provided once payment is successfully received.`,
  },
  {
    title: 'Availability',
    body: 'Redistribution or republication of any part of this Website or its content is prohibited, including by framing or other similar means, without the express written consent of the Company. The Company does not warrant that the service from this Website will be uninterrupted, timely, or error-free, although it is provided to the best of our ability. By using this service, you thereby indemnify the Company, its employees, agents, and affiliates against any loss or damage, in whatever manner, howsoever caused.',
  },
  {
    title: 'Cancellation Policy',
    body: `Cancellation may occur due to:
• The purchaser requesting cancellation.
• Non-receipt of full payment.
• Unavailability of the requested product/service.
• The supplier or brand declining to provide the product/service.
• Any technical or operational reason.

If a transaction is cancelled, any payments received will be credited back to the user's account as PayFlex Points, where 1 PayFlex Point = ₹1.`,
  },
  {
    title: 'Refund Policy',
    body: 'Refunds will be processed at the sole discretion of PayFlex. Replacement or refund policies may vary depending on the product or service. For any disputes, contact: info@payflex.in.',
  },
  {
    title: 'Intellectual Property Rights',
    body: 'Copyright and other relevant intellectual property rights exist on all text relating to the Company\'s services and the full content of this Website. All content on the Website, including text, graphics, logos, and software, is the property of PayFlex Pvt. Ltd. or its suppliers. Unauthorized use of any intellectual property is prohibited.',
  },
  {
    title: 'Force Majeure',
    body: 'Neither party shall be liable to the other for any failure to perform any obligation under any Agreement due to events beyond its control, including Acts of God, terrorism, war, political insurgence, insurrection, riot, civil unrest, earthquake, flood, or any other natural or manmade disaster. The affected party must promptly notify the other of such an event.',
  },
  {
    title: 'Arbitration and Dispute Resolution',
    body: 'All disputes not resolved informally shall be settled by binding arbitration under Indian law. Both parties waive the right to initiate or participate in class or consolidated actions. Emergency interim relief may be sought from courts under Section 9 of the Arbitration and Conciliation Act, 1996.',
  },
  {
    title: 'General',
    body: 'The laws of the Republic of India govern these terms and conditions. By accessing this Website and/or using our services, you consent to these terms and to the exclusive jurisdiction of the courts of India in all disputes arising out of such access. If any of these terms are deemed invalid or unenforceable, the invalid provision will be severed and the remaining terms will continue to apply.',
  },
  {
    title: 'Notification of Changes',
    body: 'The Company reserves the right to change these conditions from time to time. Your continued use of the Website will signify your acceptance of any adjustment to these terms. Any changes to our privacy policy will be posted on the Website 30 days prior to taking effect. You are advised to re-read this statement regularly.',
  },
  {
    title: 'Company Registration',
    body: 'This Company is registered in India as Payflex, having its registered office at Unit No. 607, 6th Floor, Capital Business Park, Sector-48, Sohna Road, Gurgaon, Haryana — 122018.',
  },
  {
    title: 'Contact Information',
    body: 'For questions or support, contact: info@payflex.in',
  },
]

export default function TermsPage() {
  return (
    <PolicyLayout
      Icon={FileText}
      iconBg="bg-gray-700"
      tag="Legal"
      title="Terms & Conditions"
      effectiveDate={null}
      intro="By accessing this Website, you are deemed to have read and agreed to the following terms and conditions. Please read them carefully. If you do not agree, please stop using the site immediately."
      sections={SECTIONS}
    />
  )
}
