<?php
$title = 'E-Resources';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">E-Resources</h1>
        <?php if ($userType !== 'student'): ?>
            <a href="/e-resources/upload" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Upload Resource
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Search/Filter could go here -->

    <?php if (empty($resources)): ?>
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <p class="text-gray-500 text-lg">No resources found.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($resources as $resource): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                    <div class="p-5">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2 truncate"
                                title="<?php echo htmlspecialchars($resource['title']); ?>">
                                <?php echo htmlspecialchars($resource['title']); ?>
                            </h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded 
                                <?php
                                echo match ($resource['status']) {
                                    'approved' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>">
                                <?php echo ucfirst($resource['status']); ?>
                            </span>
                        </div>

                        <p class="text-gray-600 text-sm mb-4 h-12 overflow-hidden">
                            <?php echo htmlspecialchars(substr($resource['description'], 0, 100)) . (strlen($resource['description']) > 100 ? '...' : ''); ?>
                        </p>

                        <div class="flex items-center text-xs text-gray-500 mb-4">
                            <span>By: <?php echo htmlspecialchars($resource['uploaderName'] ?? 'Unknown'); ?></span>
                            <span class="mx-2">•</span>
                            <span><?php echo date('M d, Y', strtotime($resource['createdAt'])); ?></span>
                        </div>

                        <div class="flex justify-between items-center mt-4 border-t pt-4">
                            <?php if ($resource['status'] === 'approved' || ($userType === 'faculty' && $resource['uploadedBy'] == $_SESSION['user_id']) || $userType === 'admin'): ?>
                                <a href="<?php echo htmlspecialchars($resource['fileUrl']); ?>" target="_blank"
                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    View/Download
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">Not available</span>
                            <?php endif; ?>

                            <!-- Admin Actions -->
                            <?php if ($userType === 'admin'): ?>
                                <div class="flex space-x-2">
                                    <?php if ($resource['status'] === 'pending'): ?>
                                        <a href="/e-resources/approve/<?php echo $resource['resourceId']; ?>"
                                            class="text-green-600 hover:text-green-800" title="Approve">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </a>
                                        <a href="/e-resources/reject/<?php echo $resource['resourceId']; ?>"
                                            class="text-red-600 hover:text-red-800" title="Reject">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/e-resources/delete/<?php echo $resource['resourceId']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this resource?')"
                                        class="text-gray-600 hover:text-red-600" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            <?php elseif ($userType === 'faculty' && $resource['uploadedBy'] == $_SESSION['user_id']): ?>
                                <a href="/e-resources/delete/<?php echo $resource['resourceId']; ?>"
                                    onclick="return confirm('Are you sure?')"
                                    class="text-red-600 hover:text-red-800 text-sm">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>