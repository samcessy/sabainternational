<?php

namespace App\Http\Controllers;

use App\Models\Redirect as RedirectModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectResolutionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $redirect = RedirectModel::query()
            ->where('from_path', '/'.ltrim($request->path(), '/'))
            ->first();

        if ($redirect === null) {
            throw new NotFoundHttpException;
        }

        return redirect($redirect->to_path, $redirect->status_code);
    }
}
