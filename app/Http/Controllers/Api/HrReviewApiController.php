<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrReview;
use App\Models\HrReviewRating;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrReviewApiController extends Controller
{
    /**
     * Submit a review with overall rating and feedback.
     */
    public function submitReview(Request $request, HrReview $review)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'overall_rating' => 'required|numeric|min:1|max:5',
            'strengths' => 'required|string|max:2000',
            'improvements' => 'required|string|max:2000',
            'comments' => 'nullable|string|max:2000',
        ]);

        $review->update([
            'overall_rating' => $validated['overall_rating'],
            'strengths' => $validated['strengths'],
            'improvements' => $validated['improvements'],
            'comments' => $validated['comments'] ?? null,
            'status' => 'submitted',
            'submitted_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'review' => $review->fresh()->load('ratings'),
        ]);
    }

    /**
     * Rate an individual KRA or goal within a review.
     */
    public function rateItem(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'review_id' => 'required|exists:hr_reviews,id',
            'kra_id' => 'nullable|exists:hr_kras,id',
            'goal_id' => 'nullable|exists:hr_goals,id',
            'rating' => 'required|numeric|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
        ]);

        // Must have either kra_id or goal_id
        if (empty($validated['kra_id']) && empty($validated['goal_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Either kra_id or goal_id is required.',
            ], 422);
        }

        $rating = HrReviewRating::updateOrCreate(
            [
                'hr_review_id' => $validated['review_id'],
                'hr_kra_id' => $validated['kra_id'] ?? null,
                'hr_goal_id' => $validated['goal_id'] ?? null,
            ],
            [
                'rating' => $validated['rating'],
                'comments' => $validated['comments'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating saved successfully.',
            'rating' => $rating,
        ]);
    }
}
