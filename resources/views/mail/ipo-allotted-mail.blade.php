<x-mail::message>
    # 🎉 Congratulations!

    You’ve successfully been **allotted shares** in the IPO of **{{ $ipo->stock->company_name }} ({{ $ipo->stock->symbol }})**.

    🔢 **Allotted Shares:** {{ $allottedShares }}
    💰 **Issue Price per Share:** Rs. {{ number_format($ipo->issue_price, 2) }}
    📈 **Total Investment:** Rs. {{ number_format($ipo->issue_price * $allottedShares, 2) }}

    <x-mail::button :url="route('user.ipo.show', $ipo->id)">
        View IPO Details
    </x-mail::button>

    If you have any questions, feel free to contact our support team.

    Thanks,
    {{ config('app.name') }}
</x-mail::message>
