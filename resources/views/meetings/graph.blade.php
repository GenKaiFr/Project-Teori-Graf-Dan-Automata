@extends('layouts.app')

@section('title', 'Graf Konflik Rapat')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">Graf Konflik Rapat</h1>
            <p class="text-gray-600 dark:text-gray-400">Visualisasi konflik antar rapat menggunakan teori graf</p>
        </div>
        <a href="{{ route('meetings.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Graph Controls -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div class="flex items-center space-x-4">
            <button onclick="resetZoom()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                <i class="fas fa-search-minus mr-2"></i>Reset Zoom
            </button>
            <button onclick="centerGraph()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                <i class="fas fa-crosshairs mr-2"></i>Center
            </button>
            <button onclick="togglePhysics()" id="physicsBtn" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">
                <i class="fas fa-pause mr-2"></i>Pause Physics
            </button>
            <button onclick="exportGraph()" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 transition">
                <i class="fas fa-download mr-2"></i>Export PNG
            </button>
        </div>
        
        <div class="flex items-center space-x-6 text-sm">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                <span class="text-gray-600 dark:text-gray-400">Scheduled</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                <span class="text-gray-600 dark:text-gray-400">Draft</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                <span class="text-gray-600 dark:text-gray-400">Ongoing</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                <span class="text-gray-600 dark:text-gray-400">Cancelled</span>
            </div>
        </div>
    </div>
    
    <div class="mt-4 flex justify-between items-center text-sm text-gray-600 dark:text-gray-400">
        <div>
            <span id="nodeCount">0</span> rapat aktif, <span id="conflictCount">0</span> konflik terdeteksi
        </div>
        <div>
            Klik node untuk detail, drag untuk memindahkan, scroll untuk zoom
        </div>
    </div>
</div>

<!-- Graph Container -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div id="graphContainer" class="w-full h-screen bg-gray-50 dark:bg-gray-900 rounded-lg"></div>
</div>

<!-- Selected Node Info -->
<div id="nodeInfo" class="fixed bottom-6 right-6 max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 hidden border-l-4 border-blue-500">
    <div class="flex justify-between items-start mb-2">
        <h4 class="font-semibold text-gray-800 dark:text-gray-200">Detail Rapat</h4>
        <button onclick="hideNodeInfo()" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div id="nodeDetails" class="text-sm text-gray-600 dark:text-gray-400"></div>
</div>

<!-- Conflict Analysis Panel -->
<div id="conflictPanel" class="fixed top-20 left-6 max-w-sm bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 hidden">
    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Analisis Konflik</h4>
    <div id="conflictAnalysis" class="text-sm text-gray-600 dark:text-gray-400 space-y-2"></div>
    <button onclick="hideConflictPanel()" class="mt-3 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">
        Tutup
    </button>
</div>

@push('scripts')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
let currentSimulation = null;
let currentSvg = null;
let physicsRunning = true;
let graphData = null;

document.addEventListener('DOMContentLoaded', function() {
    loadGraphData();
});

function loadGraphData() {
    fetch('{{ route("meetings.graphData") }}')
        .then(response => response.json())
        .then(data => {
            graphData = data;
            renderGraph(data);
            updateStats(data);
            analyzeConflicts(data);
        })
        .catch(error => {
            console.error('Error loading graph data:', error);
            document.getElementById('graphContainer').innerHTML = 
                '<div class="flex items-center justify-center h-full text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Error loading graph data</div>';
        });
}

function updateStats(data) {
    document.getElementById('nodeCount').textContent = data.nodes.length;
    document.getElementById('conflictCount').textContent = data.links.length;
}

function analyzeConflicts(data) {
    const conflictAnalysis = document.getElementById('conflictAnalysis');
    
    if (data.links.length === 0) {
        conflictAnalysis.innerHTML = '<div class="text-green-600 dark:text-green-400"><i class="fas fa-check-circle mr-2"></i>Tidak ada konflik terdeteksi</div>';
    } else {
        const conflictsByRoom = {};
        const conflictsByTime = {};
        
        data.links.forEach(link => {
            const sourceNode = data.nodes.find(n => n.id === link.source);
            const targetNode = data.nodes.find(n => n.id === link.target);
            
            if (sourceNode && targetNode) {
                // Group by room
                const room = sourceNode.room_name;
                if (!conflictsByRoom[room]) conflictsByRoom[room] = 0;
                conflictsByRoom[room]++;
                
                // Group by time
                const hour = new Date(sourceNode.start_time).getHours();
                if (!conflictsByTime[hour]) conflictsByTime[hour] = 0;
                conflictsByTime[hour]++;
            }
        });
        
        let analysisHtml = '<div class="text-red-600 dark:text-red-400 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>' + data.links.length + ' konflik terdeteksi</div>';
        
        analysisHtml += '<div class="mb-2"><strong>Per Ruangan:</strong></div>';
        Object.entries(conflictsByRoom).forEach(([room, count]) => {
            analysisHtml += `<div class="ml-2">• ${room}: ${count} konflik</div>`;
        });
        
        analysisHtml += '<div class="mt-2 mb-2"><strong>Per Jam:</strong></div>';
        Object.entries(conflictsByTime).forEach(([hour, count]) => {
            analysisHtml += `<div class="ml-2">• ${hour}:00: ${count} konflik</div>`;
        });
        
        conflictAnalysis.innerHTML = analysisHtml;
    }
    
    document.getElementById('conflictPanel').classList.remove('hidden');
}

function renderGraph(data) {
    const container = document.getElementById('graphContainer');
    container.innerHTML = '';
    
    if (data.nodes.length === 0) {
        container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400"><i class="fas fa-info-circle mr-2"></i>Tidak ada rapat aktif untuk ditampilkan</div>';
        return;
    }

    const width = container.offsetWidth;
    const height = container.offsetHeight;

    // Create SVG with zoom behavior
    const svg = d3.select('#graphContainer')
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .style('cursor', 'grab');
    
    currentSvg = svg;

    const g = svg.append('g');

    // Add zoom behavior
    const zoom = d3.zoom()
        .scaleExtent([0.1, 4])
        .on('zoom', (event) => {
            g.attr('transform', event.transform);
        });

    svg.call(zoom);

    // Create simulation with enhanced forces
    const simulation = d3.forceSimulation(data.nodes)
        .force('link', d3.forceLink(data.links).id(d => d.id).distance(150))
        .force('charge', d3.forceManyBody().strength(-500))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(40))
        .force('x', d3.forceX(width / 2).strength(0.1))
        .force('y', d3.forceY(height / 2).strength(0.1));
    
    currentSimulation = simulation;

    // Create arrow markers
    svg.append('defs').selectAll('marker')
        .data(['conflict'])
        .enter().append('marker')
        .attr('id', d => d)
        .attr('viewBox', '0 -5 10 10')
        .attr('refX', 30)
        .attr('refY', 0)
        .attr('markerWidth', 8)
        .attr('markerHeight', 8)
        .attr('orient', 'auto')
        .append('path')
        .attr('d', 'M0,-5L10,0L0,5')
        .attr('fill', '#ef4444');

    // Create links with enhanced styling
    const link = g.append('g')
        .selectAll('line')
        .data(data.links)
        .enter().append('line')
        .attr('stroke', '#ef4444')
        .attr('stroke-width', 4)
        .attr('stroke-opacity', 0.8)
        .attr('marker-end', 'url(#conflict)')
        .style('stroke-dasharray', '8,4')
        .style('animation', 'dash 2s linear infinite');

    // Create nodes with enhanced styling
    const node = g.append('g')
        .selectAll('g')
        .data(data.nodes)
        .enter().append('g')
        .style('cursor', 'pointer')
        .call(d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended))
        .on('click', (event, d) => showNodeDetails(d))
        .on('mouseenter', (event, d) => {
            // Hanya ganti highlight ke node yang diklik, tidak hilangkan konflik
            highlightSpecificNode(d);
        });

    // Add circles to nodes
    node.append('circle')
        .attr('r', 30)
        .attr('fill', d => getNodeColor(d.status))
        .attr('stroke', '#fff')
        .attr('stroke-width', 4)
        .style('filter', 'drop-shadow(3px 3px 6px rgba(0,0,0,0.3))');

    // Add status icons
    node.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', 2)
        .attr('font-size', 16)
        .attr('fill', 'white')
        .attr('font-weight', 'bold')
        .text(d => getStatusIcon(d.status));

    // Add labels
    node.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', 50)
        .attr('font-size', 12)
        .attr('font-weight', 'bold')
        .attr('fill', document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151')
        .text(d => d.title.length > 20 ? d.title.substring(0, 20) + '...' : d.title);

    // Add time labels
    node.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', 65)
        .attr('font-size', 10)
        .attr('fill', document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280')
        .text(d => d.start_time);

    // Add room labels
    node.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', 78)
        .attr('font-size', 9)
        .attr('fill', document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280')
        .text(d => d.room_name);

    // Animation for nodes
    node.style('opacity', 0)
        .transition()
        .duration(800)
        .delay((d, i) => i * 100)
        .style('opacity', 1);

    // Aktifkan visualisasi konflik permanen
    if (data.links.length > 0) {
        showAllConflicts();
    }
    
    simulation.on('tick', () => {
        link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);

        node.attr('transform', d => `translate(${d.x},${d.y})`);
    });

    // Store references
    svg.zoom = zoom;
    svg.g = g;
    
    // Prevent highlight from disappearing when moving within graph area
    svg.on('mousemove', function(event) {
        // Jangan hilangkan highlight saat bergerak di area kosong
        event.stopPropagation();
    });
    
    // Klik area kosong tidak menghilangkan visualisasi konflik
    svg.on('click', function(event) {
        if (event.target === this) {
            // Tetap tampilkan semua konflik
            showAllConflicts();
        }
    });

    function dragstarted(event, d) {
        if (!event.active && physicsRunning) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
        svg.style('cursor', 'grabbing');
    }

    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    function dragended(event, d) {
        if (!event.active && physicsRunning) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
        svg.style('cursor', 'grab');
    }
}

function getNodeColor(status) {
    switch(status) {
        case 'SCHEDULED': return '#10b981';
        case 'DRAFT': return '#f59e0b';
        case 'ONGOING': return '#3b82f6';
        case 'COMPLETED': return '#6b7280';
        case 'CANCELLED': return '#ef4444';
        default: return '#8b5cf6';
    }
}

function getStatusIcon(status) {
    switch(status) {
        case 'SCHEDULED': return '✓';
        case 'DRAFT': return '📝';
        case 'ONGOING': return '▶';
        case 'COMPLETED': return '✅';
        case 'CANCELLED': return '❌';
        default: return '?';
    }
}

let currentHighlightedNode = null;

// Fungsi untuk menampilkan semua konflik secara permanen
function showAllConflicts() {
    if (!currentSvg || !graphData) return;
    
    const nodes = currentSvg.selectAll('g g');
    const links = currentSvg.selectAll('line');
    
    // Tampilkan semua node dengan opacity penuh
    nodes.style('opacity', 1);
    
    // Tampilkan semua link konflik dengan jelas
    links.style('opacity', 1).attr('stroke-width', 4);
    
    // Reset ukuran semua node
    nodes.select('circle')
        .attr('r', 30)
        .style('filter', 'drop-shadow(3px 3px 6px rgba(0,0,0,0.3))');
}

// Fungsi untuk highlight node spesifik tanpa menghilangkan konflik
function highlightSpecificNode(nodeData) {
    if (!currentSvg) return;
    
    currentHighlightedNode = nodeData.id;
    const nodes = currentSvg.selectAll('g g');
    const links = currentSvg.selectAll('line');
    
    // Reset semua node ke ukuran normal
    nodes.select('circle')
        .attr('r', 30)
        .style('filter', 'drop-shadow(3px 3px 6px rgba(0,0,0,0.3))');
    
    // Highlight node yang dipilih
    nodes.filter(d => d.id === nodeData.id)
        .select('circle')
        .attr('r', 35)
        .style('filter', 'drop-shadow(0 0 15px rgba(59, 130, 246, 0.8))');
    
    // Semua konflik tetap terlihat
    nodes.style('opacity', 1);
    links.style('opacity', 1).attr('stroke-width', 4);
}

// Fungsi lama untuk kompatibilitas
function highlightNode(nodeData, highlight) {
    if (highlight) {
        highlightSpecificNode(nodeData);
    } else {
        showAllConflicts();
    }
}

function showNodeDetails(nodeData) {
    const nodeInfo = document.getElementById('nodeInfo');
    const nodeDetails = document.getElementById('nodeDetails');
    
    // Find conflicts for this node
    const conflicts = graphData.links.filter(link => 
        link.source === nodeData.id || link.target === nodeData.id
    );
    
    const conflictNodes = conflicts.map(link => {
        const conflictId = link.source === nodeData.id ? link.target : link.source;
        return graphData.nodes.find(n => n.id === conflictId);
    }).filter(Boolean);
    
    nodeDetails.innerHTML = `
        <div class="grid grid-cols-1 gap-2">
            <div><strong>Judul:</strong> ${nodeData.title}</div>
            <div><strong>Status:</strong> <span class="px-2 py-1 rounded text-xs" style="background-color: ${getNodeColor(nodeData.status)}20; color: ${getNodeColor(nodeData.status)}">${nodeData.status}</span></div>
            <div><strong>Ruangan:</strong> ${nodeData.room_name}</div>
            <div><strong>Waktu:</strong> ${nodeData.start_time} - ${nodeData.end_time}</div>
            <div><strong>Durasi:</strong> ${nodeData.duration || 'N/A'}</div>
            <div><strong>Peserta:</strong> ${nodeData.participants_count || 0} orang</div>
            ${conflicts.length > 0 ? `<div><strong>Konflik:</strong> ${conflicts.length} rapat</div>` : ''}
        </div>
        ${nodeData.description ? `<div class="mt-2"><strong>Deskripsi:</strong> ${nodeData.description}</div>` : ''}
        ${conflictNodes.length > 0 ? `
            <div class="mt-3">
                <strong>Konflik dengan:</strong>
                <ul class="mt-1 space-y-1">
                    ${conflictNodes.map(node => `<li class="text-red-600 dark:text-red-400">• ${node.title} (${node.room_name})</li>`).join('')}
                </ul>
            </div>
        ` : ''}
    `;
    
    nodeInfo.classList.remove('hidden');
}

function hideNodeInfo() {
    document.getElementById('nodeInfo').classList.add('hidden');
}

function hideConflictPanel() {
    document.getElementById('conflictPanel').classList.add('hidden');
}

function resetZoom() {
    if (currentSvg && currentSvg.zoom) {
        // Hanya reset zoom ke skala 1:1, tanpa mengubah posisi
        currentSvg.transition().duration(500).call(
            currentSvg.zoom.scaleTo,
            1
        );
    }
}

function centerGraph() {
    if (currentSvg && currentSvg.zoom && currentSimulation) {
        const container = document.getElementById('graphContainer');
        const width = container.offsetWidth;
        const height = container.offsetHeight;
        
        // Pusatkan graf ke tengah layar dengan animasi
        currentSvg.transition().duration(750).call(
            currentSvg.zoom.transform,
            d3.zoomIdentity.translate(0, 0).scale(1)
        );
        
        // Reorganisasi posisi node ke tengah
        currentSimulation.force('center', d3.forceCenter(width / 2, height / 2));
        currentSimulation.alpha(0.5).restart();
        
        // Reset posisi semua node yang di-drag
        if (graphData && graphData.nodes) {
            graphData.nodes.forEach(node => {
                node.fx = null;
                node.fy = null;
            });
        }
    }
}

function togglePhysics() {
    const btn = document.getElementById('physicsBtn');
    
    if (physicsRunning) {
        if (currentSimulation) currentSimulation.stop();
        btn.innerHTML = '<i class="fas fa-play mr-2"></i>Resume Physics';
        btn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
        physicsRunning = false;
    } else {
        if (currentSimulation) currentSimulation.restart();
        btn.innerHTML = '<i class="fas fa-pause mr-2"></i>Pause Physics';
        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
        btn.classList.add('bg-purple-600', 'hover:bg-purple-700');
        physicsRunning = true;
    }
}

function exportGraph() {
    if (!currentSvg) return;
    
    const svgElement = currentSvg.node();
    const serializer = new XMLSerializer();
    const svgString = serializer.serializeToString(svgElement);
    
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    canvas.width = svgElement.width.baseVal.value;
    canvas.height = svgElement.height.baseVal.value;
    
    img.onload = function() {
        ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#111827' : '#f9fafb';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        
        const link = document.createElement('a');
        link.download = 'conflict-graph-' + new Date().toISOString().slice(0, 10) + '.png';
        link.href = canvas.toDataURL();
        link.click();
    };
    
    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgString)));
}

// Add CSS animation for dashed lines
const style = document.createElement('style');
style.textContent = `
    @keyframes dash {
        to {
            stroke-dashoffset: -12;
        }
    }
`;
document.head.appendChild(style);
</script>
@endpush
@endsection