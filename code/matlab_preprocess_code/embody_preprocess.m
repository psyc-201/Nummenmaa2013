%% Short matlab demo to load and visualize emBODY data
%
% - Please note that there are hard-coded parameters related to our
% experiment version. If you modify the graphic layout of the web tool you
% will need to change also how matlab preprocesses the data
% - In our experiment we treated the left part of the screen as positive
% values and the right part as negative values, and performed a subtraction
% between the two. You are free to use the whole painting area and you
% don't need to subtract the values if left and right silouhettes have
% different meanings than activation/deactivation.
% - The demo uses the function load_subj, make sure it is in your path
% - It should be easy to rewrite this in python for those without a Matlab
% license.
%
% Code by Enrico Glerean and Lauri Nummenmaa
%
% Thanks to Laura Harrison @ caltech for useful feedback

% let's begin
close all
clear all

% get a list of subjects
cd('C:/Users/lovel/Documents/Embodiment/data_analysis/embody_matlab');
%basepath='C:/Users/lovel/Documents/Embodiment/data_analysis/embody_matlab/drawing_data_addition_1104'; % folder where the subject data is at
basepath='C:/Users/lovel/Documents/Embodiment/data_analysis/embody_matlab/drawing_data_prolific_pilot';
subjects=dir([basepath '\*']);
subjects = subjects(~ismember({subjects.name},{'.','..'})); % remove . and ..
disp("Folder names:");
disp({subjects.name}');

%%% code added - to deal with incomplete drawing data
% --- Filter subjects based on vocab_test_result.csv ---
% vocab_file = fullfile('C:/Users/lovel/Documents/Embodiment/data_analysis/vocab_test_result.csv');
vocab_file = fullfile('C:/Users/lovel/Documents/Embodiment/data_analysis/prolific_id_list.csv');
if isfile(vocab_file)
    % If file exists, filter subjects based on CSV
    subjtbl = readtable(vocab_file);      % read CSV into table
    valid_ids = string(subjtbl.id);       % list of allowed subject IDs (from "id" column)
    disp("Valid IDs from csv:");
    disp(valid_ids);

    % keep only subfolders that match IDs
    isub = [subjects(:).isdir]; 
    subjects = subjects(isub);                           % only folders
    subjects = subjects(~ismember({subjects.name},{'.','..'})); % remove . and ..
    folder_names = strtrim(string({subjects.name}));     % normalize folder names
    subjects = subjects(ismember(folder_names, valid_ids));  % keep only valid IDs

    disp("Remaining valid subject folders:");
    disp({subjects.name}');

else
    % If file is missing, proceed without filtering
    warning('vocab_test_result.csv not found. Proceeding without subject filtering.');

    % keep all valid subfolders
    isub = [subjects(:).isdir]; 
    subjects = subjects(isub);
    subjects = subjects(~ismember({subjects.name},{'.','..'})); % remove . and ..
    disp("Proceeding with all subject folders:");
    disp({subjects.name}');
end
% -------------------------------------------------------

% the base image used for painting (in our case only one sided since we
% subtract values)
base=uint8(imread('base.png'));
base2=base(10:531,33:203,:); % single image base
labels={'Neutral'
'Fear'
'Anger'
'Disgust'
'Sadness'
'Happiness'
'Surprise'
'Anxiety'
'Love'
'Depression'
'Contempt'
'Pride'
'Shame'
'Jealousy'
};
mask=imread('mask.png');

% for each subject, load data
for s=1:length(subjects)
    % skip dot and dotdot folders
    if(strcmp(subjects(s).name(1),'.')) continue; end 

    %% Data loading
    % let's load the subject's answers into a variable a
    data=load_subj([basepath '\' subjects(s).name]);
    NC=length(data); % number of conditions
    
    %% Painting reconstruction
    % 'data' now contains all mouse movements. What we need are the mouse
    % locations while the button was pressed (i.e. during painting)
    % Furthermore, the painting tool has a brush size. We recreate that
    % using image filter
    
    for n=1:NC;
        T=length(data(n).paint(:,2)); % number of mouse locations
        over=zeros(size(base,1),size(base,2)); % empty matrix to reconstruct painting
        for t=1:T
            y=ceil(data(n).paint(t,3)+1);
            x=ceil(data(n).paint(t,2)+1);
            if(x<=0) x=1; end
            if(y<=0) y=1; end
            if(x>=900) x=900; end % hardcoded for our experiment, you need to change it if you changed layout
            if(y>=600) y=600; end % hardcoded for our experiment, you need to change it if you changed layout
            over(y,x)=over(y,x)+1;
        end
        % Simulate brush size with a gaussian disk
        h=fspecial('gaussian',[15 15],5);
        over=imfilter(over,h);
        % we subtract left part minus right part of painted area
        % values are hard-coded to our web layout
        over2=over(10:531,33:203,:)-over(10:531,696:866,:);
        resmat(:,:,n)=over2;
    end
    
    %% store result    
    %outdir = fullfile('C:\Users\lovel\Documents\Embodiment\data_analysis\embody_matlab\preprocessed_data_addition_1104');
    outdir = fullfile('C:\Users\lovel\Documents\Embodiment\data_analysis\embody_matlab\preprocessed_data_prolific');
    if ~exist(outdir,'dir')
        mkdir(outdir);
    end

    if exist('resmat','var') && ~isempty(resmat)
        save(fullfile(outdir, [subjects(s).name '_preprocessed.mat']), 'resmat');
    else
        warning('resmat is empty for subject %s', subjects(s).name);
    end

    %{
    %% visualize subject's data
    M=max(abs(resmat(:))); % max range for colorbar
    NumCol=64;
    hotmap=hot(NumCol);
    coldmap=flipud([hotmap(:,3) hotmap(:,2) hotmap(:,1) ]);
    hotcoldmap=[
        coldmap
        hotmap
    ];

    % note that - for statistical maps - if you want to hide non
    % significant values and show them as black, you need to tweak the
    % colormap so that you have more rows of black between around the
    % non-significant interval. 
    % As an example if we had a threshold, uncomment the below
    if(0)
        th=0.2*M; % just an example threshold, since this is not a statistical map 
        non_sig=round(th/M*NumCol); % proportion of non significant colors
        hotmap=hot(NumCol-non_sig);
        coldmap=flipud([hotmap(:,3) hotmap(:,2) hotmap(:,1) ]);
        hotcoldmap=[
            coldmap
            zeros(2*non_sig,3);
            hotmap
        ];
    end
    
	% visualize all responses for each subject into a grid of numcolumns
	plotcols = 7; %set as desired
    plotrows = ceil((NC+1)/plotcols); % number of rows is equal to number of conditions+1 (for the colorbar)


    for n=1:NC
        figure(s)
        subplot(plotrows,plotcols,n)
        imagesc(base2);
        axis('off');
        set(gcf,'Color',[1 1 1]);
        hold on;
        over2=resmat(:,:,n);
        fh=imagesc(over2,[-M,M]);
        axis('off');
        axis equal
        colormap(hotcoldmap);
        set(fh,'AlphaData',mask)
        title(labels(n),'FontSize',10)
        if(n==NC) 
            subplot(plotrows,plotcols,n+1)
            fh=imagesc(ones(size(base2)),[-M,M]);
            axis('off');
            colorbar;
            % save a screenshot, useful for quality control (commented)
            %saveas(gcf,[subjects(s).name '.png'])
        end
    end
    %}
end





    
    

