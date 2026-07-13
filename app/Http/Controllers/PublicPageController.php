<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use App\Models\LegalDocument;
use App\Models\PlatformAdmin;
use App\Notifications\ContactInquiryReceived;
use App\Support\PublicSiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PublicPageController extends Controller
{
    public function contact()
    {
        return view('public.contact', [
            'settings' => PublicSiteSettings::all(),
            'categories' => ContactInquiry::CATEGORIES,
        ]);
    }

    public function submitContact(Request $request)
    {
        if (filled($request->input('website'))) {
            Log::info('Contact form honeypot tripped.', ['ip' => $request->ip()]);

            return back()->with('success', 'Thank you. Your message has been received.');
        }

        $rapidKey = 'contact-form:'.$request->ip().':'.strtolower((string) $request->input('email'));
        if (RateLimiter::tooManyAttempts($rapidKey, 1)) {
            return back()->withErrors(['email' => 'Please wait a moment before sending another message.'])->withInput();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'school_name' => ['nullable', 'string', 'max:160'],
            'category' => ['required', Rule::in(ContactInquiry::CATEGORIES)],
            'subject' => ['required', 'string', 'min:4', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'consent' => ['accepted'],
        ]);

        RateLimiter::hit($rapidKey, 60);

        $inquiry = ContactInquiry::create($data + [
            'status' => 'new',
            'source' => 'public_contact',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        $settings = PublicSiteSettings::all();
        $supportEmail = $settings['support_email'];

        Mail::raw($this->adminContactEmail($inquiry), function ($message) use ($inquiry, $supportEmail) {
            $message->to($supportEmail)
                ->replyTo($inquiry->email, $inquiry->name)
                ->subject('New TestServes contact inquiry: '.$inquiry->subject);
        });

        Mail::raw("Hello {$inquiry->name},\n\nThank you for contacting TestServes. We have received your message and will respond using the details you provided.\n\nTestServes Support", function ($message) use ($inquiry) {
            $message->to($inquiry->email, $inquiry->name)
                ->subject('We received your TestServes message');
        });

        if (Schema::hasTable('notifications')) {
            $admins = PlatformAdmin::whereIn('role', ['super_admin', 'support_admin'])
                ->where('is_active', true)
                ->get();
            Notification::send($admins, new ContactInquiryReceived($inquiry));
        }

        return redirect()->route('contact')->with('success', 'Your message has been sent. We will respond as soon as possible.');
    }

    public function legal(string $slug)
    {
        $document = LegalDocument::published()
            ->where('slug', $slug)
            ->latest('published_at')
            ->first();

        return view('public.legal', [
            'document' => $document,
            'fallback' => $this->fallbackLegalDocument($slug),
            'settings' => PublicSiteSettings::all(),
        ]);
    }

    public function securityPolicy()
    {
        return view('public.security-policy', [
            'settings' => PublicSiteSettings::all(),
        ]);
    }

    public function hallOfFame()
    {
        return view('public.hall-of-fame', [
            'settings' => PublicSiteSettings::all(),
        ]);
    }

    private function adminContactEmail(ContactInquiry $inquiry): string
    {
        return implode("\n", [
            "Name: {$inquiry->name}",
            "Email: {$inquiry->email}",
            'Phone: '.($inquiry->phone ?: 'Not provided'),
            'School: '.($inquiry->school_name ?: 'Not provided'),
            "Category: {$inquiry->category}",
            "Subject: {$inquiry->subject}",
            '',
            $inquiry->message,
        ]);
    }

    private function fallbackLegalDocument(string $slug): array
    {
        $map = [
            'privacy-policy' => ['Privacy Policy', $this->privacyContent()],
            'terms-of-service' => ['Terms of Service', $this->termsContent()],
            'cookie-policy' => ['Cookie Policy', $this->cookieContent()],
            'refund-policy' => ['Refund Policy', $this->refundContent()],
            'data-protection' => ['Data Protection', $this->dataProtectionContent()],
        ];

        abort_unless(isset($map[$slug]), 404);

        return [
            'title' => $map[$slug][0],
            'slug' => $slug,
            'version' => '1.0',
            'content' => $map[$slug][1],
            'effective_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function privacyContent(): string
    {
        return <<<'HTML'
<h2>Who operates TestServes</h2>
<p>TestServes is a school-focused CBT and assessment management platform. TestServes is a digital product operated by Big H Multidynamic Ventures, a CAC-registered business in Nigeria.</p>
<h2>Information we collect</h2>
<p>We may process school owner, staff and student account details, school profile and branding information, examination questions, assessment data, student results and performance records, subscription and transaction references, contact and support messages, device, browser, IP, security log, cookie and session data.</p>
<h2>How information is used</h2>
<p>Information is used for account creation and authentication, CBT and assessment services, school onboarding, subscription management, technical support, security and fraud prevention, platform improvement, and legal or operational requirements.</p>
<h2>School data</h2>
<p>Schools control the student, staff, examination and result data they enter into their portal. TestServes processes that data to provide the requested platform services. Schools are responsible for ensuring they have permission to upload and process their users' data. TestServes does not claim ownership over school academic data.</p>
<h2>Students and minors</h2>
<p>Student data may include information about minors. Schools and authorised guardians are responsible for appropriate permission and account administration.</p>
<h2>Payments and third parties</h2>
<p>Payment information may be processed by the selected payment provider, such as Paystack. TestServes does not need to store complete debit or credit card details. We may also use hosting, email and AI service providers where needed to operate platform features.</p>
<h2>Security, retention and requests</h2>
<p>We use access controls and operational safeguards to protect information. Data is retained as needed for platform operation, support, accounting, security and legal purposes. Contact us to request access, correction, deletion, or to report a privacy concern.</p>
<h2>Incidents and updates</h2>
<p>If a data incident affects platform users, TestServes will assess it and take appropriate operational steps. This policy may be updated as the service changes.</p>
HTML;
    }

    private function termsContent(): string
    {
        return <<<'HTML'
<h2>Agreement to terms</h2>
<p>By creating an account or using TestServes, you agree to these terms. TestServes is a digital product operated by Big H Multidynamic Ventures, a CAC-registered business in Nigeria.</p>
<h2>The service</h2>
<p>TestServes provides school portal, CBT, assessment, result, subscription and support tools for schools. Owners, admins, staff and students are responsible for using their accounts properly.</p>
<h2>Acceptable use</h2>
<p>You must not misuse the platform, attempt unauthorised access, interfere with examinations, upload unlawful content, abuse support channels, or use the service in a way that harms schools, students or TestServes.</p>
<h2>Subscriptions and payments</h2>
<p>Access may depend on a selected plan, trial, renewal status, payment review or expiry. TestServes may suspend access where payment, security or misuse issues require it.</p>
<h2>School data and intellectual property</h2>
<p>Schools keep control of the academic data they enter. TestServes and its operator retain rights in the platform software, branding, content and service materials.</p>
<h2>Availability, support and third-party services</h2>
<p>We work to keep TestServes reliable, but maintenance, internet issues, provider outages or school-side configuration can affect availability. Payment, email, hosting and other third-party services may be involved.</p>
<h2>Termination and changes</h2>
<p>Accounts may be closed or suspended for misuse, non-payment or operational reasons. These terms may be updated as TestServes changes.</p>
HTML;
    }

    private function cookieContent(): string
    {
        return <<<'HTML'
<h2>How TestServes uses cookies</h2>
<p>TestServes uses essential cookies and similar storage for sessions, authentication, CSRF protection, school portal routing, security and preferences. These cookies help the application work and are not used here as advertising cookies.</p>
<h2>Cookie notice</h2>
<p>You may acknowledge the cookie notice on public pages. The acknowledgement is stored so the notice does not keep appearing. Active examination pages are not interrupted by cookie notices.</p>
<h2>Changes</h2>
<p>If analytics, advertising or other non-essential cookies are added later, TestServes should identify them and provide appropriate choices where required.</p>
HTML;
    }

    private function refundContent(): string
    {
        return <<<'HTML'
<h2>Refund review</h2>
<p>Refund requests are reviewed based on the payment status, service usage and circumstances of the request.</p>
<h2>Covered situations</h2>
<p>Reviews may include duplicate payments, failed transactions, accidental overpayment, subscription purchases, and setup or onboarding charges. Completed services may be non-refundable depending on the circumstances.</p>
<h2>Information required</h2>
<p>Please provide the school name, payer name, payment reference, amount, date, payment channel and reason for the request. Approved refunds are normally returned through the original or verified payment destination where practical.</p>
HTML;
    }

    private function dataProtectionContent(): string
    {
        return <<<'HTML'
<h2>Protecting information</h2>
<p>TestServes protects information using HTTPS in transit, authentication, access controls, role-based permissions, school data separation, audit logging where implemented, and operational incident response.</p>
<h2>School responsibility</h2>
<p>Schools control the student, staff, examination and result data they enter and are responsible for appropriate permission and account administration.</p>
<h2>Requests and concerns</h2>
<p>Contact TestServes to request access, correction or deletion, or to report privacy and security concerns.</p>
HTML;
    }
}
